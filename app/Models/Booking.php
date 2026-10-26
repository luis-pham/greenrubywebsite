<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    public static function statusPending(): string
    {
        return config('statuses.booking.pending', 'pending');
    }

    public static function statusPaid(): string
    {
        return config('statuses.booking.paid', 'paid');
    }

    public static function statusConfirmed(): string
    {
        return config('statuses.booking.confirmed', 'confirmed');
    }

    public static function statusCancelled(): string
    {
        return config('statuses.booking.cancelled', 'cancelled');
    }

    public static function statusFailed(): string
    {
        return config('statuses.booking.failed', 'failed');
    }

    public static function statusLabel(string $status): string
    {
        $langKey = 'backend::booking.status_' . $status;
        $translated = __($langKey);
        if ($translated !== $langKey) {
            return $translated;
        }

        return config('statuses.booking_labels.' . $status, $status);
    }

    protected $fillable = [
        'payment_id',
        'code',
        'full_name',
        'email',
        'phone',
        'departure_date',
        'itinerary_id',
        'itinerary_name',
        'cruise_name',
        'itinerary_duration_label',
        'destination',
        'guests_total',
        'currency',
        'subtotal_cabins',
        'subtotal_amenities',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'status',
        'payment_plan',
        'payment_status',
        'amount_paid',
        'balance_due_date',
        'terms_version',
        'terms_accepted_at',
        'terms_locale',
    ];

    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_DEPOSIT_PAID = 'deposit_paid';
    public const PAYMENT_PAID = 'paid';

    /**
     * Sum of this booking's succeeded payments (the first via bookings.payment_id, later ones via
     * payments.booking_id) and the resulting payment_status. Requires migration 2026_10_27_000001.
     */
    public function refreshPaidAmount(): void
    {
        $paid = (int) Payment::where('status', Payment::statusSucceeded())
            ->where(function ($q) {
                $q->where('booking_id', $this->id);
                if ($this->payment_id) {
                    $q->orWhere('id', $this->payment_id);
                }
            })
            ->sum('amount');

        $this->update([
            'amount_paid' => $paid,
            'payment_status' => $paid <= 0 ? self::PAYMENT_UNPAID : ($paid >= (int) $this->total_amount ? self::PAYMENT_PAID : self::PAYMENT_DEPOSIT_PAID),
        ]);
    }

    /** Outstanding amount in storage units (0 for bookings made before deposits existed). */
    public function balanceAmount(): int
    {
        if ($this->payment_plan === null) {
            return 0;
        }

        return max(0, (int) $this->total_amount - (int) $this->amount_paid);
    }

    protected $casts = [
        'departure_date' => 'date',
        'balance_due_date' => 'date',
        'terms_accepted_at' => 'datetime',
        'amount_paid' => 'integer',
        'subtotal_cabins' => 'integer',
        'subtotal_amenities' => 'integer',
        'discount_amount' => 'integer',
        'tax_amount' => 'integer',
        'total_amount' => 'integer',
        'guests_total' => 'integer',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function cabins()
    {
        return $this->hasMany(BookingCabin::class);
    }

    public function amenities()
    {
        return $this->hasMany(BookingAmenity::class);
    }
}

