<?php

namespace App\Models;

use App\Payments\BookingAudit;
use App\Payments\BookingSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\BookingConfirmationMail;
use App\Mail\BookingAdminNotificationMail;

class Payment extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCEEDED = 'succeeded';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELED = 'canceled';

    public static function statusPending(): string
    {
        return config('statuses.payment.pending', self::STATUS_PENDING);
    }

    public static function statusSucceeded(): string
    {
        return config('statuses.payment.succeeded', self::STATUS_SUCCEEDED);
    }

    public static function statusFailed(): string
    {
        return config('statuses.payment.failed', self::STATUS_FAILED);
    }

    public static function statusCanceled(): string
    {
        return config('statuses.payment.canceled', self::STATUS_CANCELED);
    }

    public static function statusMessage(string $status): string
    {
        return config('statuses.payment_labels.' . $status, $status);
    }

    public static function defaultStatus(): string
    {
        return config('payment.default_status', self::statusPending());
    }

    public static function validMethods(): array
    {
        $gateways = config('payment.gateways', []);
        return $gateways ? array_keys($gateways) : config('payment.methods', ['stripe', 'paypal']);
    }

    /**
     * Applies $attributes only if the payment is still pending, under a row lock. Webhooks, the return
     * URL and status polling can report the same payment at the same moment; with the lock exactly one
     * of them moves it out of "pending", so the booking update and the emails in booted() run once.
     *
     * @return bool whether this call made the change
     */
    public function markIfPending(array $attributes): bool
    {
        $changed = DB::transaction(function () use ($attributes) {
            $current = static::whereKey($this->getKey())->lockForUpdate()->first();
            if (!$current || $current->status !== self::statusPending()) {
                return $current;
            }
            $current->update($attributes);

            return [$current];
        });

        $current = is_array($changed) ? $changed[0] : $changed;
        if ($current) {
            $this->setRawAttributes($current->getAttributes(), true);
        }

        return is_array($changed);
    }

    /** The booking this payment is for: payments.booking_id when set, otherwise bookings.payment_id. */
    public function resolveBooking(): ?Booking
    {
        if (!empty($this->attributes['booking_id'])) {
            return Booking::find($this->attributes['booking_id']);
        }

        return $this->booking()->first();
    }

    protected $fillable = [
        'booking_id',
        'internal_tx_id',
        'gateway',
        'purpose',
        'provider_tx_id',
        'amount',
        'currency',
        'status',
        'error_code',
        'error_message',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::updated(function (Payment $payment) {
            try {
                $succeeded = self::statusSucceeded();
                $failed = self::statusFailed();
                $originalStatus = $payment->getOriginal('status');

                if ($payment->status === $succeeded && $originalStatus !== $succeeded) {
                    $target = $payment->resolveBooking();
                    if ($target) {
                        $target->update(['status' => config('statuses.booking.confirmed', 'confirmed')]);
                        if (BookingSchema::ready()) {
                            $target->refreshPaidAmount();
                            BookingAudit::log($target->id, 'payment.succeeded', null, [
                                'payment_id' => $payment->id, 'gateway' => $payment->gateway, 'purpose' => $payment->purpose ?? null, 'amount' => (int) $payment->amount,
                            ]);
                        }
                    }

                    $booking = $target ? Booking::with(['cabins', 'amenities', 'payment'])->find($target->id) : null;
                    if ($booking) {
                        try {
                            $customerEmail = $booking->email ? trim((string) $booking->email) : '';
                            if ($customerEmail === '') {
                                $customer = $payment->paymentCustomers()->whereNotNull('email')->where('email', '!=', '')->first();
                                if ($customer && trim((string) $customer->email) !== '') {
                                    $customerEmail = trim((string) $customer->email);
                                    $booking->email = $customerEmail;
                                    $booking->saveQuietly();
                                    Log::info('Customer email taken from PaymentCustomer for confirmation', ['booking_code' => $booking->code, 'email' => $customerEmail]);
                                }
                            }
                            $adminEmail = config('mail.admin.address');
                            $adminEmailNormalized = $adminEmail ? trim(strtolower((string) $adminEmail)) : '';
                            $customerEmailNormalized = $customerEmail !== '' ? trim(strtolower($customerEmail)) : '';
                            if ($customerEmail !== '' && $customerEmailNormalized !== $adminEmailNormalized) {
                                try {
                                    Mail::send(new BookingConfirmationMail($booking));
                                    Log::info('Booking confirmation email sent to customer', ['booking_code' => $booking->code, 'email' => $customerEmail]);
                                } catch (\Throwable $mailEx) {
                                    Log::error('Failed to send customer confirmation email', ['booking_code' => $booking->code, 'to' => $customerEmail, 'error' => $mailEx->getMessage()]);
                                    throw $mailEx;
                                }
                            } elseif ($customerEmail !== '' && $customerEmailNormalized === $adminEmailNormalized) {
                                Log::warning('Booking customer email is admin address, skipping confirmation to avoid sending to admin', ['booking_code' => $booking->code]);
                            } else {
                                Log::warning('Customer email empty, skipping confirmation email. Check that payment init sends booking.email.', ['booking_code' => $booking->code]);
                            }
                            if ($adminEmail) {
                                Mail::to($adminEmail)->send(new BookingAdminNotificationMail($booking));
                                Log::info('Booking admin notification sent', ['booking_code' => $booking->code, 'admin_email' => $adminEmail]);
                            }
                        } catch (\Throwable $e) {
                            Log::error('Failed to send booking success emails', [
                                'booking_code' => $booking->code ?? null,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    }
                }

                if ($payment->status === $failed && $originalStatus !== $failed) {
                    $booking = $payment->booking()->with(['cabins', 'amenities'])->first();
                    // A failed balance payment must not fail a booking whose deposit was paid.
                    if ($booking && BookingSchema::ready() && (int) $booking->amount_paid > 0) {
                        $booking = null;
                    }
                    if ($booking) {
                        $booking->update(['status' => config('statuses.booking.failed', 'failed')]);
                        
                        try {
                            $adminEmail = config('mail.admin.address');
                            if ($adminEmail) {
                                Mail::to($adminEmail)->send(new BookingAdminNotificationMail($booking));
                                Log::info('Booking failed notification sent to admin', ['booking_code' => $booking->code, 'admin_email' => $adminEmail]);
                            }
                        } catch (\Throwable $e) {
                            Log::error('Failed to send payment failed notification', [
                                'booking_code' => $booking->code ?? null,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Payment booted hook error - will not affect payment status', [
                    'payment_id' => $payment->id ?? null,
                    'status' => $payment->status ?? null,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });
    }

    public function booking()
    {
        return $this->hasOne(Booking::class);
    }

    public function paymentCustomers()
    {
        return $this->hasMany(PaymentCustomer::class);
    }
}

