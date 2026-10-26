<?php

namespace App\Payments;

use Illuminate\Support\Facades\Schema;

/**
 * Whether migration 2026_10_27_000001 (payment plan, terms acceptance, payments.booking_id) has run.
 * Code that writes those columns checks this first, so this release can be deployed before the
 * migration and behaves exactly like the previous one until it runs.
 */
final class BookingSchema
{
    private static ?bool $ready = null;

    public static function ready(): bool
    {
        return self::$ready ??= Schema::hasColumn('bookings', 'terms_version')
            && Schema::hasColumn('payments', 'booking_id');
    }

    /** Tests create and drop tables; forget the cached answer. */
    public static function flush(): void
    {
        self::$ready = null;
    }
}
