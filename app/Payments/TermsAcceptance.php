<?php

namespace App\Payments;

/**
 * Rule 8: explicit acceptance of the current Booking Terms before a booking is confirmed or paid.
 * The browser sends `terms: {accepted: true, version, locale}`; the version must be the one configured
 * (config/booking.php), so a guest can never accept terms that are no longer the live ones.
 */
final class TermsAcceptance
{
    public static function required(): bool
    {
        return (bool) config('booking.terms_required') && BookingSchema::ready();
    }

    /**
     * @return array{0: ?array{terms_version: string, terms_accepted_at: \Illuminate\Support\Carbon, terms_locale: ?string}, 1: ?string}
     *         [booking attributes, null] or [null, error code]
     */
    public static function check(?array $terms): array
    {
        $current = (string) config('booking.terms_version');
        if ($current === '') {
            // Required but not configured: refuse rather than record an acceptance of nothing.
            return [null, 'terms_not_configured'];
        }
        if (!is_array($terms) || ($terms['accepted'] ?? null) !== true) {
            return [null, 'terms_required'];
        }
        if (($terms['version'] ?? null) !== $current) {
            return [null, 'terms_version_mismatch'];
        }
        return [[
            'terms_version' => $current,
            'terms_accepted_at' => now(),
            'terms_locale' => self::locale($terms['locale'] ?? null),
        ], null];
    }

    /**
     * The language of the terms the guest accepted: one the terms exist in, else 'en' (owner decision D: a
     * page in another language, e.g. ja, links to the English terms). Null when no locale was sent, as before.
     */
    public static function locale($locale): ?string
    {
        if (!is_string($locale) || trim($locale) === '') {
            return null;
        }
        $locale = strtolower(trim($locale));

        return in_array($locale, (array) config('booking.terms_locales', ['en', 'vi']), true) ? $locale : 'en';
    }
}
