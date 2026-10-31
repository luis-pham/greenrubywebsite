<?php

namespace Tests\Feature\Mail;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Booking\BookingTestCase;

/**
 * Booking e-mail fixtures on the in-memory booking schema of Tests\Feature\Booking\BookingTestCase
 * (today 27/10/2026 in Vietnam, Mail::fake()). Nothing is ever sent: mailables are only rendered.
 */
abstract class BookingMailTestCase extends BookingTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateAll();
        config([
            'mail.from.address' => 'noreply@greenruby.test',
            'app.website_link' => 'https://greenrubycruises.test',
            'booking.timezone' => 'Asia/Ho_Chi_Minh',
        ]);
    }

    /** The additive bookings.locale migration (phase 2B), run only when a test needs the column. */
    protected function migrateLocale(): void
    {
        (require database_path('migrations/2026_11_06_000001_add_locale_to_bookings.php'))->up();
    }

    /**
     * Fixture bookings. 'en': USD, full payment by card; 'en_deposit': USD deposit booking with a balance;
     * 'vi': VND by bank transfer; 'legacy': booked before terms acceptance existed (terms_locale NULL).
     */
    protected function booking(string $kind, array $extra = []): Booking
    {
        $vi = $kind === 'vi';
        $currency = $vi ? 'vnd' : 'usd';
        $total = $vi ? 25000000 : 100000;
        $gateway = ['en' => 'stripe', 'en_deposit' => 'paypal', 'vi' => 'sepay', 'legacy' => 'cash'][$kind];

        $payment = Payment::create([
            'internal_tx_id' => 'tx_' . $kind . '_' . uniqid(),
            'gateway' => $gateway,
            'amount' => $total,
            'currency' => $currency,
            'status' => 'succeeded',
        ]);
        $attributes = [
            'payment_id' => $payment->id,
            'code' => 'BK20261027' . strtoupper(substr(md5($kind), 0, 6)),
            'full_name' => $vi ? 'Nguyễn Văn An' : 'Jane Doe',
            'email' => 'guest-' . $kind . '@example.test',
            'phone' => '+84 900 000 000',
            'departure_date' => '2026-12-26',
            'itinerary_id' => 5,
            'itinerary_name' => $vi ? 'Vịnh Lan Hạ 2 ngày 1 đêm' : 'Lan Ha Bay 2 Days 1 Night',
            'cruise_name' => 'Green Ruby 2',
            'itinerary_duration_label' => $vi ? '2 ngày 1 đêm' : '2 Days 1 Night',
            'destination' => 'Lan Ha Bay',
            'guests_total' => 5,
            'currency' => $currency,
            'subtotal_cabins' => $vi ? 24000000 : 96000,
            'subtotal_amenities' => $vi ? 1000000 : 4000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => $total,
            'status' => 'confirmed',
            'payment_plan' => $kind === 'en_deposit' ? 'deposit' : ($kind === 'legacy' ? null : 'full'),
            'payment_status' => $kind === 'en_deposit' ? 'deposit_paid' : 'paid',
            'amount_paid' => $kind === 'en_deposit' ? 30000 : $total,
            'balance_due_date' => $kind === 'en_deposit' ? '2026-11-26' : null,
            'terms_version' => $kind === 'legacy' ? null : 'test-2027',
            'terms_accepted_at' => $kind === 'legacy' ? null : '2026-10-27 03:00:00',
            'terms_locale' => $kind === 'legacy' ? null : ($vi ? 'vi' : 'en'),
            'cure_deadline_at' => '2026-11-28 03:00:00',
        ];
        $booking = new Booking();
        $booking->forceFill(array_merge($attributes, $extra))->save();

        DB::table('booking_cabins')->insert([
            ['booking_id' => $booking->id, 'cabin_id' => 1, 'cabin_name' => 'Imperial Suite', 'unit_price' => $vi ? 16000000 : 64000, 'quantity' => 1, 'adults' => 2, 'children_6_12' => 1, 'children_2_5' => 0, 'infants' => 1, 'total_price' => $vi ? 16000000 : 64000],
            ['booking_id' => $booking->id, 'cabin_id' => 2, 'cabin_name' => 'Serenity Deluxe', 'unit_price' => $vi ? 8000000 : 32000, 'quantity' => 1, 'adults' => 1, 'children_6_12' => 0, 'children_2_5' => 1, 'infants' => 0, 'total_price' => $vi ? 8000000 : 32000],
        ]);
        DB::table('booking_amenities')->insert([
            ['booking_id' => $booking->id, 'amenity_id' => 3, 'amenity_name' => 'Hanoi transfer', 'unit_price' => $vi ? 500000 : 2000, 'quantity' => 2, 'total_price' => $vi ? 1000000 : 4000],
        ]);

        return $booking->fresh(['cabins', 'amenities', 'payment']);
    }

    /** Envelope and HTML of a rendered mailable, in a stable text form. */
    protected function capture(Mailable $mail): string
    {
        // Mail::fake() replaces the mailer; render with a real (array transport) one, which never sends.
        $fake = $this->app->make('mailer');
        $this->app->instance('mailer', (new \Illuminate\Mail\MailManager($this->app))->mailer('array'));
        try {
            $html = $mail->render();
        } finally {
            $this->app->instance('mailer', $fake);
        }
        $who = fn (array $list) => implode(', ', array_map(fn ($a) => trim(($a['name'] ?? '') . ' <' . $a['address'] . '>'), $list));

        return 'Subject: ' . $mail->subject . "\n"
            . 'From: ' . $who($mail->from) . "\n"
            . 'Reply-To: ' . $who($mail->replyTo) . "\n"
            . 'To: ' . $who($mail->to) . "\n"
            . "\n" . $html;
    }
}
