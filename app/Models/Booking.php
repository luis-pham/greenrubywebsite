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
        'amount_refunded',
        'payment_overdue_at',
        'cure_deadline_at',
    ];

    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_DEPOSIT_PAID = 'deposit_paid';
    public const PAYMENT_PAID = 'paid';
    /** Balance not received by the due date; the formal notice and cure period have started. */
    public const PAYMENT_OVERDUE = 'payment_overdue';
    public const PAYMENT_PARTIALLY_REFUNDED = 'partially_refunded';
    public const PAYMENT_REFUNDED = 'refunded';

    /**
     * Sum of this booking's succeeded payments (the first via bookings.payment_id, later ones via
     * payments.booking_id) and the resulting payment_status. Requires migration 2026_10_27_000001;
     * refunds and the overdue state need 2026_10_27_000002.
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
        $refunded = (int) ($this->attributes['amount_refunded'] ?? 0);

        if ($refunded > 0) {
            $status = $refunded >= $paid ? self::PAYMENT_REFUNDED : self::PAYMENT_PARTIALLY_REFUNDED;
        } elseif ($paid <= 0) {
            $status = self::PAYMENT_UNPAID;
        } elseif ($paid >= (int) $this->total_amount) {
            $status = self::PAYMENT_PAID;
        } else {
            $status = $this->payment_overdue_at ? self::PAYMENT_OVERDUE : self::PAYMENT_DEPOSIT_PAID;
        }

        $changes = ['amount_paid' => $paid, 'payment_status' => $status];
        if ($status === self::PAYMENT_PAID && array_key_exists('cure_deadline_at', $this->attributes)) {
            $changes['cure_deadline_at'] = null;
        }
        $this->update($changes);
    }

    /** Outstanding amount in storage units (0 for bookings made before deposits existed, or once refunds started). */
    public function balanceAmount(): int
    {
        if ($this->payment_plan === null || (int) ($this->attributes['amount_refunded'] ?? 0) > 0) {
            return 0;
        }

        return max(0, (int) $this->total_amount - (int) $this->amount_paid);
    }

    /** Received and not refunded, in storage units. */
    public function netPaid(): int
    {
        return max(0, (int) $this->amount_paid - (int) ($this->attributes['amount_refunded'] ?? 0));
    }

    public function refunds()
    {
        return $this->hasMany(PaymentRefund::class)->orderBy('id');
    }

    protected $casts = [
        'departure_date' => 'date',
        'balance_due_date' => 'date',
        'terms_accepted_at' => 'datetime',
        'amount_paid' => 'integer',
        'amount_refunded' => 'integer',
        'payment_overdue_at' => 'datetime',
        'cure_deadline_at' => 'datetime',
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

