<?php

namespace App\Payments\Gateways;

use App\Models\Payment;
use App\Payments\Dto\PaymentConfirmResult;
use App\Payments\Dto\PaymentInitResult;
use App\Payments\Dto\PaymentStatusResult;
use App\Payments\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\BackEnd\Helpers\Logging;

class PaypalPaymentGateway implements PaymentGatewayInterface
{
    protected $clientId;
    protected $secret;
    protected $baseUrl;
    protected $webhookId;
    protected $ordersEndpoint;
    protected $oauthEndpoint;
    protected $webhookVerifyEndpoint;

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id');
        $this->secret = config('services.paypal.secret');
        $this->baseUrl = rtrim(config('services.paypal.base_url'), '/');
        $this->webhookId = config('services.paypal.webhook_id');
        $this->ordersEndpoint = config('services.paypal.orders_endpoint');
        $this->oauthEndpoint = config('services.paypal.oauth_endpoint');
        $this->webhookVerifyEndpoint = config('services.paypal.webhook_verify_endpoint');
    }

    public function support(array $context)
    {
        return isset($context['method']) && $context['method'] === 'paypal';
    }

    protected static $unsupportedCurrencies = ['VND'];
    protected static $noDecimalCurrencies = ['VND', 'JPY', 'KRW'];

    public function initPayment(array $context)
    {
        $currencyCode = strtoupper($context['currency'] ?? '');
        if (in_array($currencyCode, self::$unsupportedCurrencies, true)) {
            throw new \RuntimeException('PayPal không hỗ trợ tiền tệ ' . $currencyCode . '. Vui lòng chọn USD hoặc dùng Stripe.');
        }

        $token = $this->getAccessToken();
        $internalTxId = $context['internal_tx_id'];
        $amount = (float) ($context['amount'] ?? 0);
        $amountValue = in_array($currencyCode, self::$noDecimalCurrencies, true)
            ? (string) (int) round($amount)
            : number_format($amount, 2, '.', '');

        $response = $this->httpClient()->withToken($token)->post($this->baseUrl . $this->ordersEndpoint, [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => ['currency_code' => $currencyCode, 'value' => $amountValue],
                    'custom_id' => $internalTxId,
                    'description' => $context['description'] ?? 'Payment',
                ],
            ],
            'application_context' => [
                'return_url' => $context['success_url'],
                'cancel_url' => $context['cancel_url'],
            ],
        ]);

        if (!$response->successful()) {
            Logging::logError('Paypal initPayment error', $response->body());
            Log::error('PayPal Orders API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Paypal initPayment failed');
        }

        $data = $response->json();
        $approveLink = collect($data['links'] ?? [])->firstWhere('rel', 'approve');
        $approvalUrl = $approveLink ? ($approveLink['href'] ?? null) : null;

        return new PaymentInitResult($internalTxId, $data['id'] ?? null, $approvalUrl);
    }

    public function confirmPayment(Request $request)
    {
        $internalTxId = $request->query('tx');
        $resultParam = $request->query('result');
        $orderId = $request->query('token') ?: null;

        if ($resultParam === 'success' && $orderId && $internalTxId) {
            $this->captureOrder($orderId, $internalTxId);
        }

        $payment = Payment::where('internal_tx_id', $internalTxId)->first();
        $status = $payment ? $payment->status : 'pending';
        return new PaymentConfirmResult($internalTxId, $status, $orderId);
    }

    protected function captureOrder(string $orderId, string $internalTxId): void
    {
        try {
            $token = $this->getAccessToken();
            $url = $this->baseUrl . $this->ordersEndpoint . '/' . $orderId . '/capture';
            $response = $this->httpClient()
                ->withToken($token)
                ->withHeaders(['Prefer' => 'return=representation'])
                ->withBody('{}', 'application/json')
                ->post($url);

            if ($response->successful()) {
                $data = $response->json();
                $orderStatus = strtoupper($data['status'] ?? '');
                $captureStatus = $this->extractCaptureStatus($data);

                if ($orderStatus === 'COMPLETED' && $captureStatus === 'COMPLETED') {
                    $payment = Payment::where('internal_tx_id', $internalTxId)->first();
                    if ($payment && $payment->status === Payment::statusPending()) {
                        $payment->update([
                            'status' => Payment::statusSucceeded(),
                            'provider_tx_id' => $orderId,
                            'completed_at' => now(),
                        ]);
                        Log::info('PayPal capture success', ['tx' => $internalTxId]);
                    }
                } elseif ($orderStatus === 'COMPLETED' && in_array($captureStatus, ['PENDING', 'ON_HOLD'], true)) {
                    $payment = Payment::where('internal_tx_id', $internalTxId)->first();
                    if ($payment && $payment->status === Payment::statusPending()) {
                        $payment->update([
                            'error_code' => 'paypal_capture_on_hold',
                            'error_message' => 'Payment is on hold by PayPal. Funds will be available when released.',
                        ]);
                        Log::info('PayPal capture on hold', ['tx' => $internalTxId, 'capture_status' => $captureStatus]);
                    }
                }
                return;
            }

            $body = $response->json();
            $message = strtolower($body['message'] ?? '');
            if (strpos($message, 'already captured') !== false || strpos($message, 'already completed') !== false) {
                $payment = Payment::where('internal_tx_id', $internalTxId)->first();
                if ($payment && $payment->status === Payment::statusPending()) {
                    $payment->update([
                        'status' => Payment::statusSucceeded(),
                        'provider_tx_id' => $orderId,
                        'completed_at' => now(),
                    ]);
                }
                return;
            }

            $details = $body['details'] ?? [];
            $firstDetail = is_array($details) ? ($details[0] ?? []) : [];
            $issue = $firstDetail['issue'] ?? 'unknown';
            $desc = $firstDetail['description'] ?? $body['message'] ?? 'Capture failed';

            $payment = Payment::where('internal_tx_id', $internalTxId)->first();
            if ($payment && $payment->status === Payment::statusPending()) {
                $payment->update([
                    'status' => Payment::statusFailed(),
                    'error_code' => 'paypal_capture_' . str_replace([' ', '-'], '_', strtolower($issue)),
                    'error_message' => $desc,
                    'completed_at' => now(),
                ]);
                Log::warning('PayPal capture failed', ['tx' => $internalTxId, 'issue' => $issue, 'body' => $body]);
            }
        } catch (\Throwable $e) {
            Log::error('PayPal capture error', ['tx' => $internalTxId, 'error' => $e->getMessage()]);
            $payment = Payment::where('internal_tx_id', $internalTxId)->first();
            if ($payment && $payment->status === Payment::statusPending()) {
                $payment->update([
                    'status' => Payment::statusFailed(),
                    'error_code' => 'paypal_capture_error',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                ]);
            }
        }
    }

    public function checkTransaction($internalTxId, array $context = [])
    {
        $payment = Payment::where('internal_tx_id', $internalTxId)->first();
        $orderId = $payment ? $payment->provider_tx_id : null;
        if (!$orderId) {
            return new PaymentStatusResult($internalTxId, 'pending');
        }

        try {
            $token = $this->getAccessToken();
            $url = $this->baseUrl . $this->ordersEndpoint . '/' . $orderId;
            $response = $this->httpClient()
                ->withToken($token)
                ->withHeaders(['Prefer' => 'return=representation'])
                ->get($url);
            if (!$response->successful()) {
                Log::debug('PayPal get order failed', ['order_id' => $orderId, 'status' => $response->status(), 'body' => $response->body()]);
                return new PaymentStatusResult($internalTxId, 'pending', $orderId);
            }

            $data = $response->json();
            $status = strtoupper($data['status'] ?? '');
            Log::debug('PayPal checkTransaction', ['tx' => $internalTxId, 'order_status' => $status]);

            $captureStatus = $this->extractCaptureStatus($data);

            if ($status === 'COMPLETED' && $captureStatus === 'COMPLETED') {
                if ($payment && $payment->status === Payment::statusPending()) {
                    $payment->update([
                        'status' => Payment::statusSucceeded(),
                        'completed_at' => now(),
                    ]);
                }
                return new PaymentStatusResult($internalTxId, 'succeeded', $orderId, $data);
            }

            if ($status === 'COMPLETED' && in_array($captureStatus, ['PENDING', 'ON_HOLD'], true)) {
                if ($payment && $payment->status === Payment::statusPending()) {
                    $payment->update([
                        'error_code' => 'paypal_capture_on_hold',
                        'error_message' => 'Payment is on hold by PayPal. Funds will be available when released.',
                    ]);
                }
                return new PaymentStatusResult($internalTxId, 'pending', $orderId, $data);
            }

            if ($status === 'APPROVED' && in_array($captureStatus, ['DECLINED', 'FAILED'], true)) {
                if ($payment && $payment->status === Payment::statusPending()) {
                    $payment->update([
                        'status' => Payment::statusFailed(),
                        'error_code' => 'paypal_capture_' . strtolower($captureStatus),
                        'error_message' => 'Payment capture ' . strtolower($captureStatus) . ' by PayPal',
                        'completed_at' => now(),
                    ]);
                }
                return new PaymentStatusResult($internalTxId, 'failed', $orderId, $data);
            }

            if ($status === 'VOIDED') {
                $errorMsg = 'Order voided by PayPal';
            } elseif (in_array($status, ['CREATED', 'SAVED'], true)) {
                $errorMsg = 'Order not completed (user cancelled or abandoned)';
            } else {
                $errorMsg = null;
            }

            if ($errorMsg !== null) {
                if ($payment && $payment->status === Payment::statusPending()) {
                    $payment->update([
                        'status' => Payment::statusCanceled(),
                        'error_code' => 'paypal_order_' . strtolower($status),
                        'error_message' => $errorMsg,
                        'completed_at' => now(),
                    ]);
                }
                return new PaymentStatusResult($internalTxId, 'canceled', $orderId, $data);
            }

            return new PaymentStatusResult($internalTxId, 'pending', $orderId, $data);
        } catch (\Throwable $e) {
            Log::debug('PayPal checkTransaction error', ['error' => $e->getMessage(), 'tx' => $internalTxId]);
            return new PaymentStatusResult($internalTxId, 'pending', $orderId);
        }
    }

    public function handleIpn(Request $request)
    {
        $token = $this->getAccessToken();

        $headers = [
            'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
            'cert_url' => $request->header('PAYPAL-CERT-URL'),
            'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
            'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
        ];

        $event = json_decode($request->getContent(), true);
        $verifyResponse = $this->httpClient()->withToken($token)->post($this->baseUrl . $this->webhookVerifyEndpoint, [
            'transmission_id' => $headers['transmission_id'],
            'transmission_time' => $headers['transmission_time'],
            'cert_url' => $headers['cert_url'],
            'auth_algo' => $headers['auth_algo'],
            'transmission_sig' => $headers['transmission_sig'],
            'webhook_id' => $this->webhookId,
            'webhook_event' => $event,
        ]);

        if (($verifyResponse->json()['verification_status'] ?? null) !== 'SUCCESS') {
            Logging::logError('Paypal webhook invalid signature', $request->getContent());
            abort(400);
        }

        $eventType = $event['event_type'] ?? null;

        if ($eventType === 'PAYMENT.CAPTURE.COMPLETED') {
            $resource = $event['resource'] ?? [];
            $internalTxId = $resource['custom_id'] ?? null;
            if ($internalTxId) {
                $payment = Payment::where('internal_tx_id', $internalTxId)->first();
                if ($payment && $payment->status === Payment::statusPending()) {
                    $payment->update([
                        'status' => Payment::statusSucceeded(),
                        'provider_tx_id' => $resource['id'] ?? $payment->provider_tx_id,
                        'completed_at' => now(),
                    ]);
                }
                Logging::logSystem('Paypal payment completed', 'tx=' . $internalTxId);
            }
        }

        if ($eventType === 'PAYMENT.CAPTURE.DENIED' || $eventType === 'CHECKOUT.PAYMENT-APPROVAL.REVERSED') {
            $resource = $event['resource'] ?? [];
            $internalTxId = $resource['custom_id'] ?? null;
            $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;
            $payment = $internalTxId ? Payment::where('internal_tx_id', $internalTxId)->first()
                : ($orderId ? Payment::where('provider_tx_id', $orderId)->first() : null);
            if ($payment && $payment->status === Payment::statusPending()) {
                $payment->update([
                    'status' => Payment::statusFailed(),
                    'error_code' => 'paypal_' . strtolower(str_replace('.', '_', $eventType)),
                    'error_message' => 'Payment denied or approval reversed by PayPal',
                    'completed_at' => now(),
                ]);
                Logging::logSystem('Paypal payment denied/reversed', 'tx=' . $payment->internal_tx_id);
            }
        }
    }

    protected function getAccessToken()
    {
        if (!$this->clientId || !$this->secret || !$this->baseUrl || !$this->oauthEndpoint) {
            throw new \RuntimeException('Paypal configuration is invalid');
        }

        $response = $this->httpClient()->withBasicAuth($this->clientId, $this->secret)->asForm()
            ->post($this->baseUrl . $this->oauthEndpoint, [
            'grant_type' => 'client_credentials',
        ]);

        if (!$response->successful()) {
            Logging::logError('Paypal getAccessToken error', $response->body());
            throw new \RuntimeException('Paypal auth failed');
        }

        $data = $response->json();
        return $data['access_token'] ?? null;
    }

    protected function httpClient()
    {
        $client = Http::getFacadeRoot();
        return config('services.payment.disable_tls_verify') ? $client->withoutVerifying() : $client;
    }

    protected function extractCaptureStatus(array $orderData): ?string
    {
        $units = $orderData['purchase_units'] ?? [];
        foreach ($units as $unit) {
            $captures = $unit['payments']['captures'] ?? [];
            foreach ($captures as $cap) {
                return strtoupper($cap['status'] ?? '');
            }
        }
        return null;
    }
}

