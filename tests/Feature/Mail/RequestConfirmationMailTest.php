<?php

namespace Tests\Feature\Mail;

use App\Mail\BookingConfirmationMail;

/**
 * Reservation request e-mail (gateway "cash", nothing charged), 2026-11-15: says the request still has to be confirmed,
 * and shows no amounts while public prices are off (frontend.showPublicPrices, the website's switch). Paid bookings
 * keep their e-mail byte for byte (LegacyBookingMailRegressionTest).
 */
class RequestConfirmationMailTest extends BookingMailTestCase
{
    public function test_request_hides_prices_while_public_prices_are_off(): void
    {
        config(['frontend.showPublicPrices' => false]);
        $out = $this->capture(new BookingConfirmationMail($this->booking('legacy')));

        $this->assertStringContainsString('Subject: Reservation Request Received - ', $out);
        $this->assertStringContainsString('your booking is confirmed only once we have done so', $out);
        $this->assertStringNotContainsString('We are pleased to confirm your reservation', $out);
        $this->assertStringNotContainsString('USD', $out);
        $this->assertStringContainsString('Estimated Total: price on request.', $out);
    }

    public function test_request_shows_prices_once_public_prices_are_on(): void
    {
        config(['frontend.showPublicPrices' => true]);
        $out = $this->capture(new BookingConfirmationMail($this->booking('legacy')));

        $this->assertStringContainsString('Subject: Reservation Request Received - ', $out);
        $this->assertStringContainsString('Estimated Total: USD 1,000.00', $out);
        $this->assertStringContainsString('Cabin Price: USD 640.00', $out);
    }

    public function test_paid_booking_keeps_the_confirmation(): void
    {
        config(['frontend.showPublicPrices' => false]);
        $out = $this->capture(new BookingConfirmationMail($this->booking('en')));

        $this->assertStringContainsString('Subject: Booking Confirmation - ', $out);
        $this->assertStringContainsString('We are pleased to confirm your reservation', $out);
        $this->assertStringContainsString('Total Amount: USD', $out);
    }
}
