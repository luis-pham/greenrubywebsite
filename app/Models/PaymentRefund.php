<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A refund requested by Reservations, decided by Finance, then recorded as made (TPX decision 5).
 * The refund itself is made by Finance in Stripe, PayPal or by bank transfer; nothing here calls a gateway.
 */
class PaymentRefund extends Model
{
    public const REQUESTED = 'requested';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const PROCESSED = 'processed';

    public const REASONS = ['guest_cancellation', 'non_payment', 'weather', 'operator_cancellation', 'other'];

    protected $fillable = [
        'booking_id', 'payment_id', 'amount', 'currency', 'reason', 'note', 'status',
        'requested_by', 'requested_at', 'decided_by', 'decided_at', 'decision_note',
        'processed_by', 'processed_at', 'provider_reference',
    ];

    protected $casts = [
        'amount' => 'integer',
        'requested_by' => 'integer',
        'decided_by' => 'integer',
        'processed_by' => 'integer',
        'requested_at' => 'datetime',
        'decided_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
