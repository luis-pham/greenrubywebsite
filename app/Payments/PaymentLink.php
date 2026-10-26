<?php

namespace App\Payments;

use App\Models\Booking;

/**
 * Signed links that let a guest pay for an existing booking themselves (TPX decisions 3 and 6):
 *  - "booking": a reservation arranged with our team; the guest accepts the Booking Terms and pays the
 *    deposit or the full amount under the same rules as the website;
 *  - "balance": the remaining amount of a deposit booking.
 * The token carries the booking code, the purpose and an expiry, signed with the app key. Amounts are
 * never in the link: they are recomputed from the booking when the guest opens and pays it.
 */
final class PaymentLink
{
    public const BOOKING = 'booking';
    public const BALANCE = 'balance';

    public static function token(Booking $booking, string $purpose, ?int $expiresAt = null): string
    {
        $payload = self::encode(json_encode([
            'c' => $booking->code,
            'p' => $purpose,
            'e' => $expiresAt ?? now()->addDays((int) config('booking.payment_link_days', 14))->getTimestamp(),
        ]));

        return $payload . '.' . self::sign($payload);
    }

    /** @return array{code: string, purpose: string, expires: int}|null null when tampered, malformed or expired */
    public static function parse(?string $token): ?array
    {
        if (!is_string($token) || substr_count($token, '.') !== 1) {
            return null;
        }
        [$payload, $signature] = explode('.', $token);
        if (!hash_equals(self::sign($payload), $signature)) {
            return null;
        }
        $data = json_decode(self::decode($payload), true);
        if (!is_array($data) || !in_array($data['p'] ?? null, [self::BOOKING, self::BALANCE], true) || !is_string($data['c'] ?? null)) {
            return null;
        }
        if ((int) ($data['e'] ?? 0) < now()->getTimestamp()) {
            return null;
        }

        return ['code' => $data['c'], 'purpose' => $data['p'], 'expires' => (int) $data['e']];
    }

    /** Public page on the website: /booking/pay (EN) or /vi/booking/pay (VI). */
    public static function url(Booking $booking, string $purpose, ?int $expiresAt = null): string
    {
        $base = rtrim((string) config('app.website_link', 'https://greenrubycruises.com'), '/');
        $path = self::locale($booking) === 'vi' ? '/vi/booking/pay' : '/booking/pay';

        return $base . $path . '?t=' . urlencode(self::token($booking, $purpose, $expiresAt));
    }

    /** Language for the guest: the one they accepted the terms in, else the booking currency's site. */
    public static function locale(Booking $booking): string
    {
        if (in_array($booking->terms_locale ?? null, ['en', 'vi'], true)) {
            return $booking->terms_locale;
        }

        return strtolower((string) $booking->currency) === 'vnd' ? 'vi' : 'en';
    }

    private static function sign(string $payload): string
    {
        return self::encode(hash_hmac('sha256', 'payment-link|' . $payload, (string) config('app.key'), true));
    }

    private static function encode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function decode(string $value): string
    {
        return (string) base64_decode(strtr($value, '-_', '+/'));
    }
}
