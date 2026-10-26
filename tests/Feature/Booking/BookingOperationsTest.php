<?php

namespace Tests\Feature\Booking;

use App\Mail\BookingPaymentMail;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentRefund;
use App\Payments\BalanceCollector;
use App\Payments\PaymentLink;
use App\Payments\RefundService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * TPX decisions 3 (balance collection, never cancel), 4 (non-payment retention), 5 (refunds with approval
 * and audit trail) and 6 (guest accepts the terms and pays through a secure link). No gateway is called.
 */
class BookingOperationsTest extends BookingTestCase
{
    private const FINANCE = 2;     // has role 9 (config booking.finance_roles)
    private const FINANCE_2 = 3;   // also Finance
    private const RESERVATIONS = 1;
    private const ACCEPT = ['accepted' => true, 'version' => 'test-2027', 'locale' => 'en'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateAll();
        config(['booking.deposit_enabled' => true, 'booking.balance_collection_enabled' => true, 'booking.terms_required' => true, 'booking.terms_version' => 'test-2027']);
        DB::table('ad_user')->insert([['id' => 1, 'username' => 'reservations'], ['id' => 2, 'username' => 'finance'], ['id' => 3, 'username' => 'finance2']]);
        DB::table('ad_user_role')->insert([['user_id' => 1, 'role_id' => 2], ['user_id' => 2, 'role_id' => 9], ['user_id' => 3, 'role_id' => 9]]);
    }

    /** A confirmed booking departing 26/12/2026 with its 30% deposit (USD 300 of 1,000) received. */
    private function depositBooking(): Booking
    {
        $this->init(['terms' => self::ACCEPT])->assertOk()->assertJson(['payment_plan' => 'deposit']);
        Payment::first()->markIfPending(['status' => Payment::statusSucceeded(), 'completed_at' => now()]);
        Mail::fake(); // forget the confirmation emails

        return Booking::first();
    }

    private function onDay(string $date, string $time = '09:00:00'): void
    {
        Carbon::setTestNow(Carbon::parse("$date $time", 'Asia/Ho_Chi_Minh'));
        CarbonImmutable::setTestNow(CarbonImmutable::parse("$date $time", 'Asia/Ho_Chi_Minh'));
    }

    private function collect(): array
    {
        return (new BalanceCollector())->run();
    }

    private function sentTypes(): array
    {
        return DB::table('booking_notifications')->orderBy('id')->pluck('type')->all();
    }

    private function guestMails(string $type): int
    {
        return Mail::sent(BookingPaymentMail::class, fn ($m) => $m->type === $type && !$m->staff)->count();
    }

    // --- Decision 3: balance collection ------------------------------------------------------

    public function test_the_command_does_nothing_while_switched_off(): void
    {
        $this->depositBooking();
        config(['booking.balance_collection_enabled' => false]);
        $this->onDay('2026-11-11');

        $this->artisan('bookings:collect-balances')->expectsOutputToContain('switched off')->assertExitCode(0);
        $this->assertSame([], $this->sentTypes());
        Mail::assertNothingSent();
    }

    public function test_request_reminders_and_formal_notice_follow_the_schedule_and_never_cancel(): void
    {
        $booking = $this->depositBooking();

        $this->onDay('2026-11-10'); // D-46: nothing yet
        $this->assertSame([], $this->collect());

        $this->onDay('2026-11-11'); // D-45
        $this->collect();
        $this->collect(); // same day again: idempotent
        $this->assertSame(['balance_request'], $this->sentTypes());
        $this->assertSame(1, $this->guestMails('balance_request'));

        $this->onDay('2026-11-19'); // D-37
        $this->collect();
        $this->onDay('2026-11-24'); // D-32, Reservations copied
        $this->collect();
        Mail::assertSent(BookingPaymentMail::class, fn ($m) => $m->type === 'balance_reminder_2' && $m->staff && $m->hasTo('reservations@example.test'));

        $this->onDay('2026-11-26'); // D-30: due date passed unpaid
        $this->collect();
        $booking->refresh();
        $this->assertSame('payment_overdue', $booking->payment_status);
        $this->assertSame('2026-11-28 09:00', $booking->cure_deadline_at->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i'));
        $this->assertSame(1, $this->guestMails('overdue_notice'));

        $this->onDay('2026-11-28', '09:30:00'); // cure period over
        $this->collect();
        $this->collect();
        $this->assertSame(['balance_request', 'balance_reminder_1', 'balance_reminder_2', 'overdue_notice', 'cure_period_ended'], $this->sentTypes());
        Mail::assertSent(BookingPaymentMail::class, fn ($m) => $m->type === 'cure_period_ended' && $m->staff);

        // Never cancelled or failed automatically, nothing kept or refunded.
        $booking->refresh();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame(30000, $booking->amount_paid);
        $this->assertSame(0, PaymentRefund::count());
        $this->assertSame(5, DB::table('booking_audit_log')->where('action', 'like', 'notification.%')->count());
    }

    public function test_a_late_first_run_sends_only_the_latest_stage(): void
    {
        $this->depositBooking();
        $this->onDay('2026-11-25'); // D-31: request and both reminders are due
        $this->collect();

        $this->assertSame(0, $this->guestMails('balance_request'));
        $this->assertSame(0, $this->guestMails('balance_reminder_1'));
        $this->assertSame(1, $this->guestMails('balance_reminder_2'));
        $this->assertSame(['balance_request', 'balance_reminder_1', 'balance_reminder_2'], $this->sentTypes());
        $this->assertSame(2, DB::table('booking_notifications')->where('meta', 'like', '%"skipped":true%')->count());
    }

    public function test_paying_the_balance_through_the_link_stops_the_reminders(): void
    {
        $booking = $this->depositBooking();
        $this->onDay('2026-11-11');
        $this->collect();
        $token = PaymentLink::token($booking, PaymentLink::BALANCE, Carbon::parse('2026-12-26 23:59:59')->getTimestamp());

        $this->getJson('/api/booking/payment-link?t=' . urlencode($token))->assertOk()->assertJson([
            'purpose' => 'balance', 'terms_required' => false, 'methods' => ['stripe', 'paypal'],
            'booking' => ['balance_amount' => 700, 'amount_paid' => 300, 'total_amount' => 1000, 'balance_due_date' => '2026-11-26'],
        ]);
        $this->postJson('/api/booking/payment-link', ['t' => $token, 'method' => 'sepay'])->assertStatus(400); // USD booking
        $this->postJson('/api/booking/payment-link', ['t' => $token, 'method' => 'stripe'])->assertOk()->assertJson(['amount_due_now' => 700]);

        $this->assertSame(700.0, (float) end(FakeGateway::$calls)['amount']);
        $this->assertSame('Balance - ' . $booking->code, end(FakeGateway::$calls)['description']);
        $payment = Payment::latest('id')->first();
        $this->assertSame(['balance', $booking->id, 70000], [$payment->purpose, (int) $payment->booking_id, (int) $payment->amount]);
        $payment->markIfPending(['status' => Payment::statusSucceeded()]);

        $booking->refresh();
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame(100000, $booking->amount_paid);
        $this->onDay('2026-11-19');
        $this->assertSame([], $this->collect());
        $this->getJson('/api/booking/payment-link?t=' . urlencode($token))->assertStatus(409)->assertJson(['code' => 'nothing_due']);
    }

    public function test_paying_after_the_overdue_notice_clears_it(): void
    {
        $booking = $this->depositBooking();
        $this->onDay('2026-11-26');
        $this->collect();
        $this->assertSame('payment_overdue', $booking->fresh()->payment_status);

        $token = PaymentLink::token($booking, PaymentLink::BALANCE, Carbon::parse('2026-12-26')->getTimestamp());
        $this->postJson('/api/booking/payment-link', ['t' => $token, 'method' => 'paypal'])->assertOk();
        Payment::latest('id')->first()->markIfPending(['status' => Payment::statusSucceeded()]);

        $booking->refresh();
        $this->assertSame('paid', $booking->payment_status);
        $this->assertNull($booking->cure_deadline_at);
    }

    public function test_links_cannot_be_forged_or_used_after_expiry(): void
    {
        $booking = $this->depositBooking();
        $token = PaymentLink::token($booking, PaymentLink::BALANCE, Carbon::now()->addDay()->getTimestamp());
        [$payload, $sig] = explode('.', $token);

        $forged = rtrim(strtr(base64_encode(json_encode(['c' => $booking->code, 'p' => 'booking', 'e' => 9999999999])), '+/', '-_'), '=') . '.' . $sig;
        $this->getJson('/api/booking/payment-link?t=' . urlencode($forged))->assertStatus(410);
        $this->getJson('/api/booking/payment-link?t=garbage')->assertStatus(410);

        $this->onDay('2026-10-29');
        $this->getJson('/api/booking/payment-link?t=' . urlencode($token))->assertStatus(410)->assertJson(['code' => 'link_invalid']);
    }

    // --- Decision 6: direct-assisted bookings ------------------------------------------------

    private function inquiryBooking(string $date = '2026-12-26'): Booking
    {
        $this->postJson('/api/booking/inquiry', ['booking' => [
            'full_name' => 'Phone Guest', 'email' => 'phone@example.test', 'departure_date' => $date, 'itinerary_id' => '5-7',
            'cabins' => [['cabin_id' => 1, 'cabin_name' => 'Suite', 'adults' => 2]],
        ], 'terms' => self::ACCEPT])->assertStatus(201);

        return Booking::latest('id')->first();
    }

    public function test_a_guest_booking_through_our_team_accepts_the_terms_and_pays_through_the_link(): void
    {
        $booking = $this->inquiryBooking();
        $token = PaymentLink::token($booking, PaymentLink::BOOKING);

        $this->getJson('/api/booking/payment-link?t=' . urlencode($token))->assertOk()->assertJson([
            'purpose' => 'booking', 'terms_required' => true, 'terms_version' => 'test-2027',
            'plans' => [['plan' => 'deposit', 'amount_due_now' => 300, 'balance_amount' => 700, 'balance_due_date' => '2026-11-26'], ['plan' => 'full', 'amount_due_now' => 1000]],
        ]);
        $this->postJson('/api/booking/payment-link', ['t' => $token, 'method' => 'stripe'])->assertStatus(422)->assertJson(['code' => 'terms_required']);
        $this->postJson('/api/booking/payment-link', ['t' => $token, 'method' => 'stripe', 'terms' => ['accepted' => true, 'version' => 'old']])->assertStatus(409);
        $this->assertSame(0, Payment::where('booking_id', $booking->id)->where('gateway', 'stripe')->count());

        $this->postJson('/api/booking/payment-link', ['t' => $token, 'method' => 'stripe', 'payment_plan' => 'deposit', 'terms' => ['accepted' => true, 'version' => 'test-2027', 'locale' => 'en']])
            ->assertOk()->assertJson(['amount_due_now' => 300]);
        $booking->refresh();
        $this->assertSame(['test-2027', 'en', 'deposit', '2026-11-26'], [$booking->terms_version, $booking->terms_locale, $booking->payment_plan, $booking->balance_due_date->toDateString()]);

        Payment::where('booking_id', $booking->id)->where('gateway', 'stripe')->first()->markIfPending(['status' => Payment::statusSucceeded()]);
        $booking->refresh();
        $this->assertSame(['confirmed', 'deposit_paid', 30000], [$booking->status, $booking->payment_status, $booking->amount_paid]);
    }

    public function test_direct_assisted_bookings_follow_the_same_45_day_rule(): void
    {
        $booking = $this->inquiryBooking('2026-12-11'); // 45 days
        $token = PaymentLink::token($booking, PaymentLink::BOOKING);

        $this->getJson('/api/booking/payment-link?t=' . urlencode($token))->assertOk()->assertJsonCount(1, 'plans')->assertJson(['plans' => [['plan' => 'full', 'amount_due_now' => 1000]]]);
        $this->postJson('/api/booking/payment-link', ['t' => $token, 'method' => 'stripe', 'payment_plan' => 'deposit', 'terms' => ['accepted' => true, 'version' => 'test-2027']])
            ->assertOk()->assertJson(['amount_due_now' => 1000]);
    }

    public function test_links_need_configured_terms(): void
    {
        $booking = $this->inquiryBooking();
        config(['booking.terms_version' => null]);

        $this->getJson('/api/booking/payment-link?t=' . urlencode(PaymentLink::token($booking, PaymentLink::BOOKING)))->assertStatus(503)->assertJson(['code' => 'terms_not_configured']);
    }

    // --- Decisions 4 and 5: refunds ----------------------------------------------------------

    public function test_refund_suggestions_follow_the_booking_terms(): void
    {
        $booking = $this->depositBooking(); // paid 300 of 1,000, departs 26/12

        $this->assertSame(30000, RefundService::suggest($booking, 'guest_cancellation')['amount']); // 60 days: no charge
        $this->assertSame(0, RefundService::suggest($booking, 'non_payment')['amount']); // keep up to the 30% deposit, nothing more
        $this->assertSame(30000, RefundService::suggest($booking, 'weather')['amount']);
        $this->assertNull(RefundService::suggest($booking, 'other')['amount']);

        $booking->update(['amount_paid' => 100000]); // paid in full, cancelling 20 days out: 50% charge
        $this->assertSame(50000, RefundService::suggest($booking, 'guest_cancellation', '2026-12-06')['amount']);
        $this->assertSame(70000, RefundService::suggest($booking, 'non_payment')['amount']); // never keep more than 30%
    }

    public function test_refunds_need_a_finance_approver_other_than_the_requester(): void
    {
        $booking = $this->depositBooking();

        $this->assertThrowsDomain(fn () => RefundService::request($booking, 30001, 'guest_cancellation', null, self::RESERVATIONS));
        $this->assertThrowsDomain(fn () => RefundService::request($booking, 100, 'other', '', self::RESERVATIONS));
        $refund = RefundService::request($booking, 30000, 'guest_cancellation', 'Guest emailed 27/10', self::RESERVATIONS);
        $this->assertThrowsDomain(fn () => RefundService::request($booking, 1, 'weather', null, self::RESERVATIONS)); // nothing left to request

        $this->assertThrowsDomain(fn () => RefundService::decide($refund, true, null, self::RESERVATIONS)); // not Finance
        $this->assertThrowsDomain(fn () => RefundService::markProcessed($refund, 're_123', self::FINANCE)); // not approved yet

        $own = RefundService::request($this->otherPaidBooking(), 100, 'weather', null, self::FINANCE);
        $this->assertThrowsDomain(fn () => RefundService::decide($own, true, null, self::FINANCE)); // own request

        RefundService::decide($refund, true, 'OK per §5', self::FINANCE);
        $this->assertThrowsDomain(fn () => RefundService::decide($refund->fresh(), false, 'late', self::FINANCE_2)); // already decided
        $this->assertSame(0, $booking->fresh()->amount_refunded); // approval alone moves no money

        RefundService::markProcessed($refund, 're_123', self::FINANCE);
        $this->assertThrowsDomain(fn () => RefundService::markProcessed($refund->fresh(), 're_123', self::FINANCE));

        $booking->refresh();
        $this->assertSame([30000, 'refunded'], [$booking->amount_refunded, $booking->payment_status]);
        $this->assertSame('processed', $refund->fresh()->status);
        Mail::assertSent(BookingPaymentMail::class, fn ($m) => $m->type === 'refund_processed' && $m->extra['amount'] === 30000);
        $this->assertSame(['refund.requested', 'refund.approved', 'refund.processed'],
            DB::table('booking_audit_log')->where('booking_id', $booking->id)->where('action', 'like', 'refund.%')->orderBy('id')->pluck('action')->all());
    }

    public function test_rejection_needs_a_reason_and_frees_the_amount(): void
    {
        $booking = $this->depositBooking();
        $refund = RefundService::request($booking, 30000, 'guest_cancellation', null, self::RESERVATIONS);

        $this->assertThrowsDomain(fn () => RefundService::decide($refund, false, ' ', self::FINANCE));
        RefundService::decide($refund, false, 'Cancellation not received in writing', self::FINANCE);

        $this->assertSame(30000, RefundService::refundable($booking->fresh()));
        $this->assertSame('deposit_paid', $booking->fresh()->payment_status);
    }

    public function test_a_partial_refund_marks_the_booking_partially_refunded(): void
    {
        $booking = $this->depositBooking();
        $refund = RefundService::request($booking, 10000, 'other', 'Goodwill, spa closed', self::RESERVATIONS);
        RefundService::decide($refund, true, null, self::FINANCE);
        RefundService::markProcessed($refund, 'VCB-778899', self::FINANCE_2);

        $this->assertSame(['partially_refunded', 10000, 0], [$booking->fresh()->payment_status, $booking->fresh()->amount_refunded, $booking->fresh()->balanceAmount()]);
    }

    private function otherPaidBooking(): Booking
    {
        $this->init(['terms' => self::ACCEPT], '2026-12-12')->assertOk();
        $payment = Payment::latest('id')->first();
        $payment->markIfPending(['status' => Payment::statusSucceeded()]);

        return $payment->resolveBooking();
    }

    private function assertThrowsDomain(callable $fn): void
    {
        try {
            $fn();
        } catch (\DomainException $e) {
            $this->addToAssertionCount(1);

            return;
        }
        $this->fail('Expected a DomainException');
    }
}
