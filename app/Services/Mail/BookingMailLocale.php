<?php

namespace App\Services\Mail;

use App\Models\Booking;
use Carbon\CarbonInterface;
use Modules\BackEnd\Helpers\Utilities;

/**
 * Which language a guest booking e-mail is written in (i18n phase 2B).
 *
 * The guest's language is bookings.locale (migration 2026_11_06_000001), written when the booking is created.
 * A booking gets e-mails in its own language only when ALL of these hold:
 *  - the locale is stored (NULL, a missing column or junk = today's behaviour);
 *  - the locale has its own templates (DRAFTED; en and vi are deliberately not in it: their e-mails are the
 *    pre-2B ones, byte for byte, whatever the stored value);
 *  - the locale is enabled for mail (config i18n.mail.locales, env I18N_MAIL_LOCALES, default "en,vi").
 * Otherwise for() returns null and every mailable renders exactly what it rendered before phase 2B.
 */
final class BookingMailLocale
{
    /** Locales with their own guest templates: resources/views/emails/{locale}/ and app/Mail/Copy/{locale}.php. */
    public const DRAFTED = ['ja'];

    /** The locale for this booking's guest e-mails, or null for today's (legacy) e-mail. */
    public static function for(Booking $booking): ?string
    {
        $locale = self::stored($booking);

        return $locale !== null && in_array($locale, self::DRAFTED, true) && in_array($locale, self::enabled(), true)
            ? $locale
            : null;
    }

    /** bookings.locale normalised ('ja', 'ja-JP' → 'ja'), or null when absent or not a language code. */
    public static function stored(Booking $booking): ?string
    {
        return self::normalize($booking->getAttributes()['locale'] ?? null);
    }

    /**
     * The value to store in bookings.locale for a request's language: the primary subtag in lower case
     * ('ja-JP' → 'ja'), or null when nothing usable was sent. Meant for the booking controllers.
     */
    public static function normalize($locale): ?string
    {
        if (!is_string($locale)) {
            return null;
        }
        $primary = strtolower(explode('-', str_replace('_', '-', trim($locale)), 2)[0]);

        return preg_match('/^[a-z]{2,3}$/', $primary) ? $primary : null;
    }

    /**
     * Attributes to merge into Booking::create() for the request's language: ['locale' => 'ja'], or [] when
     * nothing usable was sent or migration 2026_11_06_000001 has not run (so creation never breaks before it).
     */
    public static function attributes($requestLocale): array
    {
        $locale = self::normalize($requestLocale);

        return $locale !== null && self::columnExists() ? ['locale' => $locale] : [];
    }

    private static ?bool $column = null;

    public static function columnExists(): bool
    {
        return self::$column ??= \Illuminate\Support\Facades\Schema::hasColumn('bookings', 'locale');
    }

    /** Tests create and drop tables; forget the cached answer. */
    public static function flush(): void
    {
        self::$column = null;
    }

    /** @return string[] locales enabled for guest e-mails */
    public static function enabled(): array
    {
        $value = config('i18n.mail.locales', ['en', 'vi']);
        $list = is_array($value) ? $value : explode(',', (string) $value);

        return array_values(array_filter(array_map(fn ($code) => strtolower(trim((string) $code)), $list)));
    }

    /** @return array<string, mixed> app/Mail/Copy/{locale}.php */
    public static function copy(string $locale): array
    {
        static $cache = [];

        return $cache[$locale] ??= require app_path('Mail/Copy/' . $locale . '.php');
    }

    /** "US$1,250" / "US$1,250.50" for USD (ja), else the legacy "VND 1,000,000" form. */
    public static function money($stored, ?string $currency): string
    {
        $currency = strtolower((string) ($currency ?: 'usd'));
        $number = Utilities::formatStoredAmount((int) $stored, $currency, false);
        if ($currency !== 'usd') {
            return strtoupper($currency) . ' ' . $number;
        }

        return 'US$' . (str_ends_with($number, '.00') ? substr($number, 0, -3) : $number);
    }

    /** 2027年2月15日. Date-only columns are formatted as stored; instants in Vietnam time. */
    public static function date(?CarbonInterface $date, bool $instant = false): string
    {
        if ($date === null) {
            return '';
        }
        $date = $instant ? $date->copy()->setTimezone(config('booking.timezone', 'Asia/Ho_Chi_Minh')) : $date;

        return $date->format('Y年n月j日');
    }

    /** 2027年2月15日 15:00（ベトナム時間） */
    public static function dateTime(?CarbonInterface $date): string
    {
        return $date === null ? '' : self::date($date, true) . ' ' . $date->copy()->setTimezone(config('booking.timezone', 'Asia/Ho_Chi_Minh'))->format('H:i') . '（ベトナム時間）';
    }

    /** Public URL of the Booking Terms, optionally at a section (#cancellation, #refunds, #payment). */
    public static function termsUrl(?string $section = null): string
    {
        $base = rtrim((string) config('app.website_link', 'https://greenrubycruises.com'), '/');

        return $base . config('i18n.mail.terms_path', '/booking-terms-and-conditions') . ($section ? '#' . $section : '');
    }
}
