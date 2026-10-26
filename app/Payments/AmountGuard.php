<?php

namespace App\Payments;

use App\Models\Payment;
use Illuminate\Support\Facades\Log;

/**
 * Checks that what a gateway reports as paid is exactly what we asked for.
 * Payment::amount is stored in minor units for USD (cents) and whole units for VND.
 */
final class AmountGuard
{
    /** Major-unit amount ("123.45" USD, "2500000" VND) to the Payment::amount storage format. */
    public static function toStorage($amount, string $currency): int
    {
        $amount = (float) $amount;

        return strtolower($currency) === 'usd' ? (int) round($amount * 100) : (int) round($amount);
    }

    public static function matches(Payment $payment, int $storageAmount, ?string $currency = null): bool
    {
        if ($currency !== null && strtolower($currency) !== strtolower((string) $payment->currency)) {
            return false;
        }

        return $storageAmount === (int) $payment->amount;
    }

    /**
     * Marks a pending payment as failed because the paid amount differs from the expected one.
     * The Payment model then fails the booking and notifies the admin, who must refund manually.
     */
    public static function failMismatch(Payment $payment, string $gateway, int $reportedAmount, ?string $reportedCurrency): void
    {
        Log::critical('Payment amount mismatch: manual review/refund required', [
            'tx' => $payment->internal_tx_id,
            'gateway' => $gateway,
            'expected_amount' => $payment->amount,
            'expected_currency' => $payment->currency,
            'reported_amount' => $reportedAmount,
            'reported_currency' => $reportedCurrency,
        ]);

        if ($payment->status === Payment::statusPending()) {
            $payment->markIfPending([
                'status' => Payment::statusFailed(),
                'error_code' => 'amount_mismatch',
                'error_message' => sprintf(
                    'Gateway reported %d %s, expected %d %s. Review and refund if needed.',
                    $reportedAmount,
                    strtoupper((string) $reportedCurrency),
                    $payment->amount,
                    strtoupper((string) $payment->currency)
                ),
                'completed_at' => now(),
            ]);
        }
    }
}
