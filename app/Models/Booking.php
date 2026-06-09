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
    ];

    protected $casts = [
        'departure_date' => 'date',
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

