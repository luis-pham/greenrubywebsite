<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingCabin extends Model
{
    protected $fillable = [
        'booking_id',
        'cabin_id',
        'cabin_name',
        'cabin_description',
        'unit_price',
        'quantity',
        'adults',
        'children_6_12',
        'children_2_5',
        'infants',
        'total_price',
    ];

    protected $casts = [
        'unit_price' => 'integer',
        'total_price' => 'integer',
        'quantity' => 'integer',
        'adults' => 'integer',
        'children_6_12' => 'integer',
        'children_2_5' => 'integer',
        'infants' => 'integer',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}

