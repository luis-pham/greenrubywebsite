<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentCustomer extends Model
{
    protected $fillable = [
        'payment_id',
        'full_name',
        'email',
        'phone',
        'country',
        'city',
        'address_line1',
        'address_line2',
        'postal_code',
        'nationality',
        'ip_address',
        'user_agent',
        'gateway_payload',
        'gateway_request_id',
    ];

    protected $casts = [
        'gateway_payload' => 'array',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}

