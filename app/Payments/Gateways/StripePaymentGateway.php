<?php

namespace App\Payments\Gateways;

use App\Models\Payment;
use App\Payments\AmountGuard;
use App\Payments\Dto\PaymentConfirmResult;
use App\Payments\Dto\PaymentInitResult;
use App\Payments\Dto\PaymentStatusResult;
use App\Payments\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\BackEnd\Helpers\Logging;

class StripePaymentGateway implements PaymentGatewayInterface
{
    protected const SIGNATURE_TOLERANCE_SECONDS = 300;

    protected $secret;
    protected $webhookSecret;
    protected $apiBase;
    protected $checkoutSessionEndpoint;

    public function __construct()
    {
        $this->secret = config('services.stripe.secret');
        $this->webhookSecret = config('services.stripe.webhook_secret');
        $this->apiBase = rtrim(config('services.stripe.api_base'), '/');
        $this->checkoutSessionEndpoint = config('services.stripe.checkout_session_endpoint');

        if (!$this->secret || !$this->webhookSecret || !$this->apiBase || !$this->checkoutSessionEndpoint) {
            throw new \RuntimeException('Stripe configuration is invalid');
        }
    }

    public function support(array $context)
    {
        return isset($context['method']) && $context['method'] === 'stripe';
    }

    public function initPayment(array $context)
    {
        $internalTxId = $context['internal_tx_id'];
        $currency = strtolower($context['currency'] ?? 'usd');
        $amount = (float) $context['amount'];
        $zeroDecimalCurrencies = ['jpy', 'krw', 'vnd', 'vuv', 'xaf', 'xof', 'xpf'];
        if (in_array($currency, $zeroDecimalCurrencies, true)) {
            $unitAmount = (int) round($amount);
        } else {
            $unitAmount = (int) round($amount * 100);
        }

        $url = $this->apiBase . $this->checkoutSessionEndpoint;
        $request = $this->httpClient()->withToken($this->secret)->asForm();
        $response = $request->post($url, [
                'mode' => 'payment',
                'success_url' => $context['success_url'],
                'cancel_url' => $context['cancel_url'],
                'metadata[internal_tx_id]' => $internalTxId,
                // Also on the PaymentIntent, so payment_intent.* webhooks can be matched to the payment.
                'payment_intent_data[metadata][internal_tx_id]' => $internalTxId,
                'line_items[0][price_data][currency]' => $currency,
                'line_items[0][price_data][unit_amount]' => $unitAmount,
                'line_items[0][price_data][product_data][name]' => $context['description'] ?? 'Payment',
                'line_items[0][quantity]' => 1,
            ]);

        if (!$response->successful()) {
            Logging::logError('Stripe initPayment error', $response->body());
            throw new \RuntimeException('Stripe initPayment failed');
        }

        $data = $response->json();

        return new PaymentInitResult(
            $internalTxId,
            $data['id'] ?? null,
            $data['url'] ?? null
        );
    }

    public function confirmPayment(Request $request)
    {
        $internalTxId = $request->query('tx');
        return new PaymentConfirmResult($internalTxId, 'pending');
    }

    public function checkTransaction($internalTxId, array $context = [])
    {
        $payment = Payment::where('internal_tx_id', $internalTxId)->first();
        $sessionId = $payment ? $payment->provider_tx_id : null;
        if (!$sessionId) {
            return new PaymentStatusResult($internalTxId, 'pending');
        }

        $url = $this->apiBase . '/v1/checkout/sessions/' . $sessionId;
        $response = $this->httpClient()->withToken($this->secret)->get($url);
        if (!$response->successful()) {
            return new PaymentStatusResult($internalTxId, 'pending', $sessionId);
        }

        $data = $response->json();
        $status = $data['status'] ?? null;
        $paymentStatus = $data['payment_status'] ?? null;

        if ($status === 'complete' && $paymentStatus === 'paid') {
            if ($payment && $payment->status === Payment::statusPending()) {
                if (!$this->sessionAmountMatches($payment, $data)) {
                    return new PaymentStatusResult($internalTxId, 'failed', $sessionId, $data);
                }
                $payment->markIfPending([
                    'status' => Payment::statusSucceeded(),
                    'completed_at' => now(),
                ]);
            }
            return new PaymentStatusResult($internalTxId, 'succeeded', $sessionId, $data);
        }
        if ($status === 'expired') {
            if ($payment && $payment->status === Payment::statusPending()) {
                $payment->markIfPending([
                    'status' => Payment::statusCanceled(),
                    'completed_at' => now(),
                ]);
            }
            return new PaymentStatusResult($internalTxId, 'canceled', $sessionId, $data);
        }

        return new PaymentStatusResult($internalTxId, 'pending', $sessionId, $data);
    }

    public function handleIpn(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        $event = json_decode($payload, true);
        $type = $event['type'] ?? null;

        if (!$this->verifySignature($payload, $signature, $this->webhookSecret)) {
            Logging::logError('Stripe webhook invalid signature', $payload);
            abort(400);
        }

        $object = $event['data']['object'] ?? [];

        // "completed" can arrive before the money does (async methods); only "paid" counts.
        $paid = ($object['payment_status'] ?? null) === 'paid';
        if (($type === 'checkout.session.completed' && $paid) || $type === 'checkout.session.async_payment_succeeded') {
            $internalTxId = $object['metadata']['internal_tx_id'] ?? null;
            if ($internalTxId) {
                $payment = Payment::where('internal_tx_id', $internalTxId)->first();
                if ($payment && $payment->status === Payment::statusPending() && $this->sessionAmountMatches($payment, $object)) {
                    $payment->markIfPending([
                        'status' => Payment::statusSucceeded(),
                        'provider_tx_id' => $object['id'] ?? $payment->provider_tx_id,
                        'completed_at' => now(),
                    ]);
                }
                Logging::logSystem('Stripe payment completed', 'tx=' . $internalTxId);
            }
        }

        if ($type === 'payment_intent.payment_failed') {
            $internalTxId = $object['metadata']['internal_tx_id'] ?? null;
            if ($internalTxId) {
                $payment = Payment::where('internal_tx_id', $internalTxId)->first();
                if ($payment && $payment->status === Payment::statusPending()) {
                    $payment->markIfPending([
                        'status' => Payment::statusFailed(),
                        'error_code' => $object['last_payment_error']['code'] ?? null,
                        'error_message' => $object['last_payment_error']['message'] ?? null,
                        'completed_at' => now(),
                    ]);
                }
                Logging::logSystem('Stripe payment failed', 'tx=' . $internalTxId);
            }
        }
    }

    protected function verifySignature($payload, $signatureHeader, $secret)
    {
        if (!$payload || !$signatureHeader || !$secret) {
            return false;
        }

        $parts = explode(',', $signatureHeader);
        $timestamp = null;
        $signature = null;

        foreach ($parts as $part) {
            $kv = explode('=', $part, 2);
            if (count($kv) !== 2) {
                continue;
            }
            if ($kv[0] === 't') {
                $timestamp = $kv[1];
            }
            if ($kv[0] === 'v1') {
                $signature = $kv[1];
            }
        }

        if (!$timestamp || !$signature) {
            return false;
        }

        // Reject replays of old (validly signed) events; Stripe's default tolerance is 5 minutes.
        if (abs(time() - (int) $timestamp) > self::SIGNATURE_TOLERANCE_SECONDS) {
            return false;
        }

        $signedPayload = $timestamp . '.' . $payload;
        $expected = hash_hmac('sha256', $signedPayload, $secret);

        if (function_exists('hash_equals')) {
            return hash_equals($expected, $signature);
        }

        return $expected === $signature;
    }

    /** Checkout Session amount_total is in the smallest unit (cents for USD, whole VND), like Payment::amount. */
    protected function sessionAmountMatches(Payment $payment, array $session): bool
    {
        $reported = (int) ($session['amount_total'] ?? -1);
        $currency = $session['currency'] ?? null;
        if (AmountGuard::matches($payment, $reported, $currency)) {
            return true;
        }
        AmountGuard::failMismatch($payment, 'stripe', $reported, $currency);

        return false;
    }

    protected function httpClient()
    {
        $client = Http::getFacadeRoot();
        return config('services.payment.disable_tls_verify') ? $client->withoutVerifying() : $client;
    }
}

