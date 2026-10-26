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
use App\Payments\BookingAudit;
use Tests\TestCase;

/**
 * In-memory database with the booking tables as they exist in production (created from the original SQL
 * dump, so there is no migration for them), a fake gateway and a fake pricer (USD 1,000.00).
 * Today is 2026-10-27 in Vietnam. Scheduled departures of itinerary 5 on cruise 7: 26/10 (past),
 * 26/11 (30 days), 27/11 (31), 11/12 (45), 12/12 (46) and 26/12 (60).
 */
abstract class BookingTestCase extends TestCase
{
    protected const TODAY = '2026-10-27';

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
        Schema::create('ad_user', function (Blueprint $t) {
            $t->increments('id');
            $t->string('username')->nullable();
        });
        Schema::create('ad_user_role', function (Blueprint $t) {
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('role_id');
        });
        DB::table('app_cabin')->insert(['id' => 1, 'language_id' => 2]);
        foreach (['2026-11-26', '2026-11-27', '2026-12-26', '2026-10-26', '2026-12-11', '2026-12-12'] as $i => $date) {
            DB::table('app_cruise_itinerary')->insert(['id' => $i + 1, 'cruise_id' => 7, 'itinerary_id' => 5, 'start_at' => $date . ' 08:00:00']);
        }

        config([
            'payment.gateways.stripe' => FakeGateway::class,
            'booking.deposit_enabled' => false,
            'booking.terms_required' => false,
            'booking.terms_version' => null,
            'booking.deposit_percent' => 30,
            'booking.balance_days_before' => 30,
            'booking.deposit_min_days' => 45,
            'booking.balance_collection_enabled' => false,
            'booking.reservations_email' => 'reservations@example.test',
            'booking.finance_roles' => [9],
            'mail.admin.address' => 'admin@example.test',
        ]);
        $this->app->bind(BookingPricer::class, FakePricer::class);
        BookingSchema::flush();
        BookingAudit::flush();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();
        BookingSchema::flush();
        BookingAudit::flush();
        parent::tearDown();
    }

    protected function migrate(): void
    {
        (require database_path('migrations/2026_10_27_000001_add_payment_plan_and_terms_to_bookings.php'))->up();
        BookingSchema::flush();
    }

    /** Both booking migrations (payment plan and terms; balance collection, refunds, audit log). */
    protected function migrateAll(): void
    {
        $this->migrate();
        (require database_path('migrations/2026_10_27_000002_create_booking_payment_operations_tables.php'))->up();
        BookingAudit::flush();
    }

    protected function init(array $extra = [], string $date = '2026-12-26')
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
