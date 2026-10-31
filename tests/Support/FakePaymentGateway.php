<?php

namespace Tests\Support;

use App\Models\Payment;
use App\Payments\Dto\PaymentConfirmResult;
use App\Payments\Dto\PaymentInitResult;
use App\Payments\Dto\PaymentStatusResult;
use App\Payments\PaymentGatewayInterface;
use Illuminate\Http\Request;

/**
 * Local browser tests only (tests/e2e/cms-router.php): stands in for every payment provider. No network call, no
 * money. initPayment "redirects" straight back to the success URL; checkTransaction settles the payment with
 * E2E_FAKE_PAYMENT_OUTCOME (succeeded | pending | failed, default succeeded).
 */
class FakePaymentGateway implements PaymentGatewayInterface
{
    public function support(array $context)
    {
        return true;
    }

    public function initPayment(array $context)
    {
        return new PaymentInitResult($context['internal_tx_id'], 'fake_' . $context['internal_tx_id'], $context['success_url']);
    }

    public function confirmPayment(Request $request)
    {
        return new PaymentConfirmResult($request->query('tx'), 'pending');
    }

    public function checkTransaction($internalTxId, array $context = [])
    {
        $outcome = getenv('E2E_FAKE_PAYMENT_OUTCOME') ?: 'succeeded';
        $payment = Payment::where('internal_tx_id', $internalTxId)->first();
        if ($payment && $outcome !== 'pending' && $payment->status === Payment::statusPending()) {
            $payment->markIfPending([
                'status' => $outcome === 'failed' ? Payment::statusFailed() : Payment::statusSucceeded(),
                'completed_at' => now(),
            ]);
        }

        return new PaymentStatusResult($internalTxId, $outcome, 'fake_' . $internalTxId);
    }

    public function handleIpn(Request $request)
    {
        return null;
    }
}
