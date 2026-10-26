<?php

namespace App\Payments;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only history of what happened to a booking's money: links sent, reminders, overdue notices,
 * refund requests and decisions (TPX decision 5). Rows are never updated or deleted by the application.
 */
final class BookingAudit
{
    private static ?bool $ready = null;

    /** Migration 2026_10_27_000002 has run (balance collection, refunds, audit log). */
    public static function ready(): bool
    {
        return self::$ready ??= BookingSchema::ready() && Schema::hasTable('booking_audit_log') && Schema::hasTable('payment_refunds');
    }

    public static function flush(): void
    {
        self::$ready = null;
    }

    public static function log(int $bookingId, string $action, ?int $userId = null, array $data = []): void
    {
        if (!self::ready()) {
            return;
        }
        DB::table('booking_audit_log')->insert([
            'booking_id' => $bookingId,
            'action' => $action,
            'user_id' => $userId,
            'data' => $data ? json_encode($data, JSON_UNESCAPED_UNICODE) : null,
            'created_at' => now(),
        ]);
    }
}
