<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingAmenity extends Model
{
    protected $fillable = [
        'booking_id',
        'amenity_id',
        'amenity_name',
        'unit_price',
        'quantity',
        'total_price',
    ];

    protected $casts = [
        'unit_price' => 'integer',
        'total_price' => 'integer',
        'quantity' => 'integer',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}

