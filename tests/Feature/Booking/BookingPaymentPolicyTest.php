<?php

namespace Tests\Feature\Booking;

use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use App\Models\Payment;
use App\Payments\BookingPricer;
use App\Payments\BookingSchema;
use App\Payments\Dto\PaymentInitResult;
use App\Payments\PaymentGatewayInterface;
use App\Payments\PaymentPlan;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Bootstrap\BootProviders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Deposit/full payment (rules 1-3), Booking Terms acceptance (rule 8) and idempotent status changes,
 * on an in-memory database with the booking tables as they exist in production. Nothing reaches a real
 * gateway: "stripe" is bound to FakeGateway and prices come from FakePricer (USD 1,000.00).
 */
class BookingPaymentPolicyTest extends TestCase
{
    private const TODAY = '2026-10-27';

    public function createApplication()
    {
        $app = require __DIR__ . '/../../../bootstrap/app.php';
        $app->beforeBootstrapping(BootProviders::class, function ($app) {
            $schema = $app['db']->connection()->getSchemaBuilder();
            $schema->create('ad_language', function (Blueprint $t) {
                $t->increments('id');
                $t->string('code', 5);
                $t->boolean('is_default')->default(false);
                $t->unsignedInteger('ord')->default(0);
            });
            $schema->create('ad_resource', function (Blueprint $t) {
                $t->increments('id');
                $t->string('alias');
            });
            $schema->create('ad_privilege', function (Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('resource_id');
                $t->string('alias');
            });
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse(self::TODAY . ' 10:00:00', 'Asia/Ho_Chi_Minh'));
        CarbonImmutable::setTestNow(CarbonImmutable::parse(self::TODAY . ' 10:00:00', 'Asia/Ho_Chi_Minh'));
        Mail::fake();
        FakeGateway::$calls = [];

        DB::table('ad_language')->insert([['id' => 1, 'code' => 'vi', 'is_default' => true], ['id' => 2, 'code' => 'en', 'is_default' => false]]);
        Schema::create('ad_config', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('language_id')->nullable();
            $t->string('key');
            $t->text('value')->nullable();
            $t->unsignedInteger('ord')->default(0);
        });
        // Production tables (created from the original SQL dump, no migration in the repository).
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->string('internal_tx_id', 64)->unique();
            $t->string('gateway', 32);
            $t->string('provider_tx_id', 128)->nullable();
            $t->unsignedBigInteger('amount');
            $t->string('currency', 10);
            $t->string('status', 32)->default('pending');
            $t->string('error_code', 64)->nullable();
            $t->text('error_message')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        });
        Schema::create('bookings', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('payment_id')->nullable();
            $t->string('code', 32)->unique();
            $t->string('full_name');
            $t->string('email')->nullable();
            $t->string('phone', 32)->nullable();
            $t->date('departure_date')->nullable();
            $t->unsignedBigInteger('itinerary_id')->nullable();
            $t->string('itinerary_name')->nullable();
            $t->string('cruise_name')->nullable();
            $t->string('itinerary_duration_label', 32)->nullable();
            $t->string('destination')->nullable();
            $t->unsignedInteger('guests_total')->default(0);
            $t->string('currency', 10)->default('usd');
            foreach (['subtotal_cabins', 'subtotal_amenities', 'discount_amount', 'tax_amount', 'total_amount'] as $c) {
                $t->unsignedBigInteger($c)->default(0);
            }
            $t->string('status', 32)->default('pending');
            $t->timestamps();
        });
        Schema::create('booking_cabins', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('booking_id');
            $t->unsignedBigInteger('cabin_id')->nullable();
            $t->string('cabin_name');
            $t->text('cabin_description')->nullable();
            $t->unsignedBigInteger('unit_price')->default(0);
            $t->unsignedInteger('quantity')->default(1);
            foreach (['adults', 'children_6_12', 'children_2_5', 'infants'] as $c) {
                $t->unsignedInteger($c)->default(0);
            }
            $t->unsignedBigInteger('total_price')->default(0);
            $t->timestamps();
        });
        Schema::create('booking_amenities', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('booking_id');
            $t->unsignedBigInteger('amenity_id')->nullable();
            $t->string('amenity_name');
            $t->unsignedBigInteger('unit_price')->default(0);
            $t->unsignedInteger('quantity')->default(0);
            $t->unsignedBigInteger('total_price')->default(0);
            $t->timestamps();
        });
        Schema::create('payment_customers', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('payment_id');
            $t->string('full_name');
            $t->string('email')->nullable();
            $t->string('phone', 32)->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->string('user_agent')->nullable();
            $t->json('gateway_payload')->nullable();
            $t->timestamps();
        });
        Schema::create('app_cabin', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('language_id');
        });
        Schema::create('app_cruise_itinerary', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('cruise_id');
            $t->unsignedInteger('itinerary_id');
            $t->dateTime('start_at');
        });
        DB::table('app_cabin')->insert(['id' => 1, 'language_id' => 2]);
        foreach (['2026-11-26', '2026-11-27', '2026-12-26', '2026-10-26'] as $i => $date) {
            DB::table('app_cruise_itinerary')->insert(['id' => $i + 1, 'cruise_id' => 7, 'itinerary_id' => 5, 'start_at' => $date . ' 08:00:00']);
        }

        config([
            'payment.gateways.stripe' => FakeGateway::class,
            'booking.deposit_enabled' => false,
            'booking.terms_required' => false,
            'booking.terms_version' => null,
            'booking.deposit_percent' => 30,
            'booking.balance_days_before' => 30,
            'mail.admin.address' => 'admin@example.test',
        ]);
        $this->app->bind(BookingPricer::class, FakePricer::class);
        BookingSchema::flush();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();
        BookingSchema::flush();
        parent::tearDown();
    }

    private function migrate(): void
    {
        (require database_path('migrations/2026_10_27_000001_add_payment_plan_and_terms_to_bookings.php'))->up();
        BookingSchema::flush();
    }

    private function init(array $extra = [], string $date = '2026-12-26')
    {
        return $this->postJson('/api/payment/init', array_merge([
            'method' => 'stripe',
            'amount' => 1,
            'booking' => [
                'full_name' => 'Test Guest',
                'email' => 'guest@example.test',
                'departure_date' => $date,
                'itinerary_id' => '5-7',
                'cabins' => [['cabin_id' => 1, 'cabin_name' => 'Suite', 'adults' => 2, 'quantity' => 1]],
            ],
        ], $extra));
    }

    // --- Backwards compatibility -------------------------------------------------------------

    public function test_switches_off_and_no_migration_charges_the_full_amount_as_before(): void
    {
        $res = $this->init(['payment_plan' => 'deposit', 'terms' => ['accepted' => true, 'version' => 'x']]);

        $res->assertOk()->assertJsonMissing(['payment_plan' => 'deposit']);
        $this->assertSame(1000.0, (float) FakeGateway::$calls[0]['amount']);
        $this->assertSame(100000, (int) Payment::first()->amount);
        $this->assertSame(100000, (int) Booking::first()->total_amount);
        $this->assertFalse(Schema::hasColumn('bookings', 'payment_plan'));
    }

    public function test_switches_off_after_migration_still_charges_in_full_and_records_the_plan(): void
    {
        $this->migrate();
        $this->init(['payment_plan' => 'deposit'])->assertOk();

        $booking = Booking::first();
        $payment = Payment::first();
        $this->assertSame(1000.0, (float) FakeGateway::$calls[0]['amount']);
        $this->assertSame('full', $booking->payment_plan);
        $this->assertSame('unpaid', $booking->payment_status);
        $this->assertSame('full', $payment->purpose);
        $this->assertSame($booking->id, (int) $payment->booking_id);
    }

    public function test_deposit_switch_is_ignored_until_the_migration_has_run(): void
    {
        config(['booking.deposit_enabled' => true]);
        $this->init()->assertOk();
        $this->assertSame(1000.0, (float) FakeGateway::$calls[0]['amount']);
    }

    // --- Rules 1-3 ---------------------------------------------------------------------------

    public function test_more_than_30_days_out_defaults_to_a_30_percent_deposit(): void
    {
        $this->migrate();
        config(['booking.deposit_enabled' => true]);

        $this->init()->assertOk()->assertJson([
            'payment_plan' => 'deposit',
            'amount_due_now' => 300,
            'balance_amount' => 700,
            'balance_due_date' => '2026-11-26',
        ]);
        $this->assertSame(300.0, (float) FakeGateway::$calls[0]['amount']);
        $this->assertStringStartsWith('Deposit 30% - ', FakeGateway::$calls[0]['description']);
        $this->assertSame(30000, (int) Payment::first()->amount);
        $this->assertSame(100000, (int) Booking::first()->total_amount);
        $this->assertSame('deposit', Payment::first()->purpose);
    }

    public function test_full_payment_can_be_chosen_when_a_deposit_is_allowed(): void
    {
        $this->migrate();
        config(['booking.deposit_enabled' => true]);

        $this->init(['payment_plan' => 'full'])->assertOk()->assertJson(['payment_plan' => 'full', 'amount_due_now' => 1000, 'balance_amount' => 0]);
        $this->assertSame(1000.0, (float) FakeGateway::$calls[0]['amount']);
        $this->assertNull(Booking::first()->balance_due_date);
    }

    public function test_exactly_30_days_out_requires_full_payment_and_31_allows_a_deposit(): void
    {
        $this->migrate();
        config(['booking.deposit_enabled' => true]);

        $this->init(['payment_plan' => 'deposit'], '2026-11-26')->assertOk()->assertJson(['payment_plan' => 'full', 'amount_due_now' => 1000]);
        $this->init(['payment_plan' => 'deposit'], '2026-11-27')->assertOk()->assertJson(['payment_plan' => 'deposit', 'amount_due_now' => 300]);
    }

    public function test_a_departure_that_is_not_scheduled_or_in_the_past_is_refused(): void
    {
        $this->migrate();
        config(['booking.deposit_enabled' => true]);

        // A fake far-away date would otherwise unlock a 30% deposit for a near departure.
        $this->init([], '2027-03-01')->assertStatus(422)->assertJson(['code' => 'departure_date_unknown']);
        $this->init([], '2026-10-26')->assertStatus(422)->assertJson(['code' => 'departure_date_past']);
        $this->init([], '')->assertStatus(422)->assertJson(['code' => 'departure_date_required']);
        $this->assertSame([], FakeGateway::$calls);
        $this->assertSame(0, Payment::count());
    }

    public function test_deposit_rounding_keeps_the_total_exact(): void
    {
        $this->assertSame(['due_now' => 370370.0, 'balance' => 864197.0], PaymentPlan::amounts(1234567, 'vnd', 'deposit'));
        $this->assertSame(['due_now' => 370.37, 'balance' => 864.2], PaymentPlan::amounts(1234.57, 'usd', 'deposit'));
        $this->assertSame(['due_now' => 1234.57, 'balance' => 0.0], PaymentPlan::amounts(1234.57, 'usd', 'full'));
    }

    // --- Rule 8 ------------------------------------------------------------------------------

    public function test_terms_must_be_accepted_in_the_current_version(): void
    {
        $this->migrate();
        config(['booking.terms_required' => true, 'booking.terms_version' => '2027-01-01']);

        $this->init()->assertStatus(422)->assertJson(['code' => 'terms_required']);
        $this->init(['terms' => ['accepted' => false, 'version' => '2027-01-01']])->assertStatus(422);
        $this->init(['terms' => ['accepted' => true, 'version' => '2026-12-01']])->assertStatus(409)->assertJson(['code' => 'terms_version_mismatch']);
        $this->assertSame(0, Payment::count());

        $this->init(['terms' => ['accepted' => true, 'version' => '2027-01-01', 'locale' => 'en']])->assertOk();
        $booking = Booking::first();
        $this->assertSame('2027-01-01', $booking->terms_version);
        $this->assertSame('en', $booking->terms_locale);
        $this->assertSame(self::TODAY, $booking->terms_accepted_at->setTimezone('Asia/Ho_Chi_Minh')->toDateString());
        $this->assertNotEmpty($booking->code);
    }

    public function test_terms_required_without_a_configured_version_refuses_bookings(): void
    {
        $this->migrate();
        config(['booking.terms_required' => true, 'booking.terms_version' => null]);

        $this->init(['terms' => ['accepted' => true, 'version' => '']])->assertStatus(503)->assertJson(['code' => 'terms_not_configured']);
    }

    public function test_reservation_requests_record_terms_acceptance_too(): void
    {
        $this->migrate();
        config(['booking.terms_required' => true, 'booking.terms_version' => '2027-01-01']);
        $booking = ['full_name' => 'Test Guest', 'email' => 'guest@example.test', 'cabins' => [['cabin_id' => 1, 'cabin_name' => 'Suite', 'adults' => 2]], 'itinerary_id' => '5-7'];

        $this->postJson('/api/booking/inquiry', ['booking' => $booking])->assertStatus(422);
        $this->postJson('/api/booking/inquiry', ['booking' => $booking, 'terms' => ['accepted' => true, 'version' => '2027-01-01', 'locale' => 'vi']])->assertStatus(201);
        $this->assertSame('2027-01-01', Booking::first()->terms_version);
        $this->assertSame('vi', Booking::first()->terms_locale);
    }

    public function test_policy_endpoint_reports_the_switches(): void
    {
        $this->getJson('/api/booking/policy')->assertOk()->assertJson([
            'deposit_enabled' => false, 'terms_required' => false, 'terms_version' => null, 'deposit_percent' => 30, 'today' => self::TODAY,
        ]);

        $this->migrate();
        config(['booking.deposit_enabled' => true, 'booking.terms_required' => true, 'booking.terms_version' => '2027-01-01']);
        $this->getJson('/api/booking/policy')->assertOk()->assertJson(['deposit_enabled' => true, 'terms_required' => true, 'terms_version' => '2027-01-01']);
    }

    // --- Idempotency and booking state -------------------------------------------------------

    public function test_a_payment_reported_twice_is_confirmed_and_emailed_once(): void
    {
        $this->migrate();
        config(['booking.deposit_enabled' => true]);
        $this->init()->assertOk();
        $payment = Payment::first();
        $duplicate = Payment::first(); // a second request holding its own copy, as a webhook and a poll would

        $this->assertTrue($payment->markIfPending(['status' => Payment::statusSucceeded(), 'completed_at' => now()]));
        $this->assertFalse($duplicate->markIfPending(['status' => Payment::statusSucceeded(), 'completed_at' => now()]));
        $this->assertSame('succeeded', $duplicate->status);

        Mail::assertSent(BookingConfirmationMail::class, 1);
        $booking = Booking::first();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('deposit_paid', $booking->payment_status);
        $this->assertSame(30000, $booking->amount_paid);
        $this->assertSame(70000, $booking->balanceAmount());
    }

    public function test_a_late_failure_does_not_undo_a_succeeded_payment(): void
    {
        $this->init()->assertOk();
        $payment = Payment::first();
        $payment->markIfPending(['status' => Payment::statusSucceeded()]);

        $this->assertFalse(Payment::first()->markIfPending(['status' => Payment::statusFailed()]));
        $this->assertSame('succeeded', Payment::first()->status);
        $this->assertSame('confirmed', Booking::first()->status);
    }

    public function test_full_payment_marks_the_booking_paid(): void
    {
        $this->migrate();
        $this->init()->assertOk();
        Payment::first()->markIfPending(['status' => Payment::statusSucceeded()]);

        $this->assertSame('paid', Booking::first()->payment_status);
        $this->assertSame(0, Booking::first()->balanceAmount());
    }

    public function test_status_endpoint_returns_the_balance_to_the_token_holder(): void
    {
        $this->migrate();
        config(['booking.deposit_enabled' => true]);
        $token = $this->init()->json('status_token');
        $tx = Payment::first()->internal_tx_id;
        Payment::first()->markIfPending(['status' => Payment::statusSucceeded()]);

        $this->getJson('/api/payment/status?internal_tx_id=' . urlencode($tx) . '&token=' . $token)->assertOk()->assertJson([
            'status' => 'succeeded',
            'booking' => ['payment_plan' => 'deposit', 'amount_paid' => 300, 'balance_amount' => 700, 'balance_due_date' => '2026-11-26', 'total_amount' => 1000],
        ]);
    }

    public function test_rollback_migration_restores_the_original_tables(): void
    {
        $this->migrate();
        (require database_path('migrations/2026_10_27_000001_add_payment_plan_and_terms_to_bookings.php'))->down();
        BookingSchema::flush();

        $this->assertFalse(Schema::hasColumn('bookings', 'payment_plan'));
        $this->assertFalse(Schema::hasColumn('payments', 'booking_id'));
        $this->init()->assertOk();
        $this->assertSame(1000.0, (float) FakeGateway::$calls[0]['amount']);
    }
}

/** Never calls a payment provider; remembers what it was asked to charge. */
class FakeGateway implements PaymentGatewayInterface
{
    public static array $calls = [];

    public function support(array $context)
    {
        return true;
    }

    public function initPayment(array $context)
    {
        self::$calls[] = $context;

        return new PaymentInitResult($context['internal_tx_id'], 'fake_' . count(self::$calls), 'https://pay.example.test/' . count(self::$calls));
    }

    public function confirmPayment(Request $request)
    {
        return null;
    }

    public function checkTransaction($internalTxId, array $context = [])
    {
        return null;
    }

    public function handleIpn(Request $request)
    {
    }
}

/** USD 1,000.00 for any booking with a cabin, like BookingPricer would compute from the database. */
class FakePricer extends BookingPricer
{
    public function price(array $bookingData): ?array
    {
        if (empty($bookingData['cabins'])) {
            return $this->fail('no_cabins');
        }

        return [
            'currency' => 'usd',
            'cabins' => [['cabin_id' => 1, 'unit_price' => 1000.0, 'quantity' => 1, 'total_price' => 1000.0]],
            'amenities' => [],
            'subtotal_cabins' => 1000.0,
            'subtotal_amenities' => 0.0,
            'total' => 1000.0,
        ];
    }
}
