<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\Payment;
use App\Payments\PaymentLink;
use App\Services\Mail\BookingMailLocale;
use Carbon\Carbon;

/**
 * i18n phase 2B payment guard (config booking.payment_locales): a page language outside the list cannot start an
 * online payment; the reservation request stays open. EN/VI, and requests that send no locale, are unchanged.
 * Also: Japanese guest data (kanji/kana names, +81 phones) passes the server validation, and bookings.locale is
 * stored for the mailer. Nothing reaches a real gateway (BookingTestCase FakeGateway).
 */
class BookingPaymentLocaleTest extends BookingTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        BookingMailLocale::flush();
    }

    protected function tearDown(): void
    {
        BookingMailLocale::flush();
        parent::tearDown();
    }

    private function migrateLocale(): void
    {
        (require database_path('migrations/2026_11_06_000001_add_locale_to_bookings.php'))->up();
        BookingMailLocale::flush();
    }

    private function inquiry(array $extra = [], array $booking = [])
    {
        return $this->postJson('/api/booking/inquiry', array_merge([
            'booking' => array_merge([
                'full_name' => 'Test Guest',
                'email' => 'guest@example.test',
                'phone' => '+84 900 000 000',
                'departure_date' => '2026-12-26',
                'itinerary_id' => '5-7',
                'currency' => 'usd',
                'cabins' => [['cabin_id' => 1, 'cabin_name' => 'Suite', 'adults' => 2, 'quantity' => 1]],
            ], $booking),
        ], $extra));
    }

    // --- Policy endpoint ---------------------------------------------------------------------

    public function test_policy_reports_the_payment_locales(): void
    {
        $this->getJson('/api/booking/policy')->assertOk()->assertJson(['payment_locales' => ['en', 'vi']]);

        config(['booking.payment_locales' => ['en', 'vi', 'JA ']]);
        $this->getJson('/api/booking/policy')->assertOk()->assertJson(['payment_locales' => ['en', 'vi', 'ja']]);
    }

    public function test_the_default_list_comes_from_the_environment_as_en_and_vi(): void
    {
        $this->assertSame(['en', 'vi'], (require config_path('booking.php'))['payment_locales']);
    }

    // --- POST /api/payment/init ----------------------------------------------------------------

    public function test_payments_without_a_locale_are_unchanged(): void
    {
        $this->init()->assertOk()->assertJsonStructure(['payment_url', 'internal_tx_id', 'status_token']);
        $this->assertCount(1, FakeGateway::$calls);
        $this->assertSame(1000.0, (float) FakeGateway::$calls[0]['amount']);
    }

    public function test_english_and_vietnamese_pages_still_pay(): void
    {
        $this->init(['locale' => 'en'])->assertOk();
        $this->init(['locale' => 'vi'])->assertOk();
        $this->init(['locale' => 'en-GB'])->assertOk();
        $this->assertCount(3, FakeGateway::$calls);
        $this->assertSame(3, Payment::count());
    }

    public function test_japanese_is_refused_by_default_before_anything_is_stored_or_charged(): void
    {
        $this->init(['locale' => 'ja'])->assertStatus(422)->assertJson(['success' => false, 'code' => 'payment_locale_not_allowed', 'locale' => 'ja']);
        $this->init(['locale' => 'ja-JP'])->assertStatus(422)->assertJson(['code' => 'payment_locale_not_allowed']);

        $this->assertSame([], FakeGateway::$calls);
        $this->assertSame(0, Payment::count());
        $this->assertSame(0, Booking::count());
    }

    public function test_japanese_pays_once_it_is_allowed(): void
    {
        config(['booking.payment_locales' => ['en', 'vi', 'ja']]);
        $this->migrateLocale();

        $this->init(['locale' => 'ja'])->assertOk();
        $this->assertCount(1, FakeGateway::$calls);
        $this->assertSame('usd', Payment::first()->currency);
        $this->assertSame(100000, (int) Booking::first()->total_amount, 'same amount as an English booking');
        $this->assertSame('ja', Booking::first()->getAttributes()['locale']);
    }

    // --- POST /api/booking/inquiry (no payment): always open ---------------------------------

    public function test_a_japanese_reservation_request_is_accepted_with_japanese_guest_data(): void
    {
        $this->migrateLocale();

        $this->inquiry(['locale' => 'ja'], ['full_name' => '山田 太郎（ヤマダ タロウ）', 'phone' => '+81 90-1234-5678'])
            ->assertStatus(201)->assertJson(['success' => true]);

        $booking = Booking::first();
        $this->assertSame('山田 太郎（ヤマダ タロウ）', $booking->full_name);
        $this->assertSame('+81 90-1234-5678', $booking->phone);
        $this->assertSame('usd', $booking->currency);
        $this->assertSame('ja', $booking->getAttributes()['locale']);
        $this->assertSame([], FakeGateway::$calls);
    }

    public function test_inquiry_validation_still_refuses_a_missing_name_and_a_bad_email(): void
    {
        $this->inquiry(['locale' => 'ja'], ['full_name' => ''])->assertStatus(422);
        $this->inquiry(['locale' => 'ja'], ['email' => 'not-an-email'])->assertStatus(422);
        $this->inquiry(['locale' => str_repeat('x', 17)])->assertStatus(422);
        $this->assertSame(0, Booking::count());
    }

    public function test_requests_without_the_locale_column_or_value_are_stored_as_before(): void
    {
        $this->inquiry(['locale' => 'ja'])->assertStatus(201);
        $this->inquiry()->assertStatus(201);
        $this->assertSame(2, Booking::count());

        $this->migrateLocale();
        $this->inquiry()->assertStatus(201);
        $this->assertNull(Booking::latest('id')->first()->getAttributes()['locale']);
    }

    // --- POST /api/booking/payment-link -------------------------------------------------------

    private function japaneseBookingLink(): string
    {
        $this->migrateAll();
        $this->migrateLocale();
        config(['booking.terms_version' => '2027-01-01']);
        $this->inquiry(['locale' => 'ja'])->assertStatus(201);

        return PaymentLink::token(Booking::first(), PaymentLink::BOOKING, Carbon::parse('2026-12-26 23:59:59')->getTimestamp());
    }

    public function test_a_payment_link_of_a_japanese_booking_is_refused_by_default(): void
    {
        $token = $this->japaneseBookingLink();
        $accept = ['accepted' => true, 'version' => '2027-01-01', 'locale' => 'en'];

        // From the English page too: the booking was made in Japanese.
        $this->postJson('/api/booking/payment-link', ['t' => $token, 'method' => 'stripe', 'terms' => $accept, 'locale' => 'en'])
            ->assertStatus(422)->assertJson(['code' => 'payment_locale_not_allowed']);
        $this->assertSame([], FakeGateway::$calls);

        config(['booking.payment_locales' => ['en', 'vi', 'ja']]);
        $this->postJson('/api/booking/payment-link', ['t' => $token, 'method' => 'stripe', 'terms' => $accept, 'locale' => 'ja'])->assertOk();
        $this->assertCount(1, FakeGateway::$calls);
    }

    public function test_a_payment_link_opened_on_a_japanese_page_is_refused_by_default(): void
    {
        $this->migrateAll();
        config(['booking.terms_version' => '2027-01-01']);
        $this->inquiry()->assertStatus(201); // an English booking, no stored locale
        $token = PaymentLink::token(Booking::first(), PaymentLink::BOOKING, Carbon::parse('2026-12-26 23:59:59')->getTimestamp());
        $accept = ['accepted' => true, 'version' => '2027-01-01', 'locale' => 'en'];

        $this->postJson('/api/booking/payment-link', ['t' => $token, 'method' => 'stripe', 'terms' => $accept, 'locale' => 'ja'])
            ->assertStatus(422)->assertJson(['code' => 'payment_locale_not_allowed']);
        $this->postJson('/api/booking/payment-link', ['t' => $token, 'method' => 'stripe', 'terms' => $accept])->assertOk();
        $this->assertCount(1, FakeGateway::$calls);
    }
}
