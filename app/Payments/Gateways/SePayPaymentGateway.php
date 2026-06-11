<?php

namespace App\Payments\Gateways;

use App\Models\Payment;
use App\Payments\Dto\PaymentConfirmResult;
use App\Payments\Dto\PaymentInitResult;
use App\Payments\Dto\PaymentStatusResult;
use App\Payments\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SePayPaymentGateway implements PaymentGatewayInterface
{
    protected string $merchantId;
    protected string $secretKey;
    protected string $baseUrl;
    protected string $pgUrl;

    public function __construct()
    {
        $this->merchantId = trim((string) config('services.sepay.merchant_id'));
        $this->secretKey = trim((string) config('services.sepay.secret_key'));
        $this->baseUrl = rtrim((string) config('services.sepay.base_url'), '/');
        $this->pgUrl = rtrim((string) config('services.sepay.pg_url'), '/');

        if ($this->merchantId === '' || $this->secretKey === '' || !$this->baseUrl || !$this->pgUrl) {
            throw new \RuntimeException('SePay configuration is invalid');
        }
    }

    public function support(array $context)
    {
        return isset($context['method']) && $context['method'] === 'sepay';
    }

    protected static function sepayInvoiceNumber(string $internalTxId): string
    {
        return str_replace('.', '_', $internalTxId);
    }

    protected static function pickOrderRow(array $orders, string $orderInvoiceNumber): ?array
    {
        $want = strtolower($orderInvoiceNumber);
        foreach ($orders as $row) {
            if (!is_array($row)) {
                continue;
            }
            $inv = isset($row['order_invoice_number']) ? strtolower((string) $row['order_invoice_number']) : '';
            if ($inv !== '' && $inv === $want) {
                return $row;
            }
        }

        return null;
    }

    public function initPayment(array $context)
    {
        $internalTxId = $context['internal_tx_id'];
        $amount = (int) $context['amount'];
        $description = $context['description'] ?? ('Payment ' . $internalTxId);

        $successUrl = $context['success_url'];
        $cancelUrl = $context['cancel_url'];
        $errorUrl = $cancelUrl;

        $orderInvoiceNumber = self::sepayInvoiceNumber($internalTxId);
        $customerId = self::sepayInvoiceNumber($context['customer_id'] ?? $internalTxId);

        $fields = [
            'merchant' => $this->merchantId,
            'currency' => 'VND',
            'order_amount' => (string) $amount,
            'operation' => 'PURCHASE',
            'order_description' => $description,
            'order_invoice_number' => $orderInvoiceNumber,
            'customer_id' => $customerId,
            'success_url' => $successUrl,
            'error_url' => $errorUrl,
            'cancel_url' => $cancelUrl,
        ];

        $fields['signature'] = $this->signFields($fields, $this->secretKey);

        $cacheKey = 'sepay_form_' . $internalTxId;
        Cache::put($cacheKey, $fields, 900);

        $formPageUrl = url('/payment/sepay/form?tx=' . urlencode($internalTxId));

        return new PaymentInitResult($internalTxId, $internalTxId, $formPageUrl);
    }

    public function confirmPayment(Request $request)
    {
        $internalTxId = $request->query('tx');
        $payment = $internalTxId
            ? Payment::where('internal_tx_id', $internalTxId)->first()
            : null;

        $status = $payment ? $payment->status : 'pending';

        return new PaymentConfirmResult($internalTxId, $status, $internalTxId);
    }

    public function checkTransaction($internalTxId, array $context = [])
    {
        $payment = Payment::where('internal_tx_id', $internalTxId)->first();
        if (!$payment) {
            return new PaymentStatusResult($internalTxId, Payment::statusPending());
        }

        $orderInvoiceNumber = self::sepayInvoiceNumber($internalTxId);

        try {
            $url = $this->pgUrl . '/v1/order';
            $order = null;
            $json = [];
            for ($page = 1; $page <= 5; $page++) {
                $response = $this->httpClient()
                    ->withBasicAuth($this->merchantId, $this->secretKey)
                    ->get($url, [
                        'per_page' => 50,
                        'page' => $page,
                        'sort' => 'created_at:desc',
                    ]);

                if (!$response->successful()) {
                    Log::warning('SePay list orders HTTP error', [
                        'tx' => $internalTxId,
                        'invoice' => $orderInvoiceNumber,
                        'status' => $response->status(),
                    ]);
                    return new PaymentStatusResult($internalTxId, Payment::statusPending(), $payment->provider_tx_id);
                }

                $json = $response->json();
                $orders = $json['data'] ?? [];
                if (!is_array($orders) || empty($orders)) {
                    break;
                }

                $order = self::pickOrderRow($orders, $orderInvoiceNumber);
                if ($order) {
                    break;
                }
            }

            if (!$order || !isset($order['order_status'])) {
                if (config('app.debug')) {
                    Log::debug('SePay list orders: no matching row for invoice', [
                        'tx' => $internalTxId,
                        'invoice' => $orderInvoiceNumber,
                        'returned_count' => count($orders),
                        'json' => $json,
                    ]);
                }
                return new PaymentStatusResult($internalTxId, Payment::statusPending(), $payment->provider_tx_id, $json);
            }

            $orderStatus = strtolower((string) $order['order_status']);
            $status = match ($orderStatus) {
                'captured', 'approved' => Payment::statusSucceeded(),
                'cancelled' => Payment::statusFailed(),
                default => Payment::statusPending(),
            };

            if ($payment->status === Payment::statusPending() && $status !== Payment::statusPending()) {
                $update = [
                    'status' => $status,
                    'completed_at' => now(),
                ];

                if ($status === Payment::statusFailed()) {
                    $update['error_code'] = 'sepay_' . $orderStatus;
                    $update['error_message'] = 'Thanh toán qua SePay bị hủy/thất bại.';
                }

                if ($status === Payment::statusSucceeded() && !empty($order['order_id'])) {
                    $update['provider_tx_id'] = (string) $order['order_id'];
                }

                $payment->update($update);
            }

            return new PaymentStatusResult(
                $internalTxId,
                $status,
                $payment->provider_tx_id,
                $order
            );
        } catch (\Throwable $e) {
            Log::debug('SePay checkTransaction error', ['tx' => $internalTxId, 'error' => $e->getMessage()]);
            return new PaymentStatusResult($internalTxId, Payment::statusPending(), $payment->provider_tx_id ?? null);
        }
    }

    public function handleIpn(Request $request)
    {
        $ipnSecret = trim((string) config('services.sepay.webhook_secret'));
        if ($ipnSecret === '') {
            Log::warning('SePay IPN rejected: SEPAY_WEBHOOK_SECRET is not configured');
            abort(401);
        }
        $headerKey = (string) $request->header('X-Secret-Key', '');
        if (!hash_equals($ipnSecret, $headerKey)) {
            Log::warning('SePay IPN rejected: invalid X-Secret-Key');
            abort(401);
        }

        $payload = $request->getContent();
        $data = json_decode($payload, true) ?: [];

        if (isset($data['payload']) && is_array($data['payload'])) {
            $data = $data['payload'];
        }

        $invoice = $data['order_invoice_number']
            ?? $data['order_code']
            ?? (isset($data['order']) ? ($data['order']['order_invoice_number'] ?? null) : null);
        $notificationType = $data['notification_type'] ?? null;
        $orderStatus = isset($data['order']['order_status'])
            ? strtolower($data['order']['order_status'])
            : null;
        $txStatus = isset($data['transaction']['transaction_status'])
            ? strtolower($data['transaction']['transaction_status'])
            : null;
        $status = $data['status'] ?? $orderStatus ?? $txStatus ?? '';
        $status = strtolower((string) $status);
        $providerTxId = $data['transaction_id']
            ?? (isset($data['transaction']) ? ($data['transaction']['transaction_id'] ?? null) : null);

        if (!$invoice) {
            Log::warning('SePay webhook missing invoice', ['payload' => $data]);
            return;
        }

        $lastUnderscore = strrpos($invoice, '_');
        $internalTxId = $lastUnderscore !== false
            ? substr($invoice, 0, $lastUnderscore) . '.' . substr($invoice, $lastUnderscore + 1)
            : $invoice;
        $payment = Payment::where('internal_tx_id', $invoice)->first()
            ?? Payment::where('internal_tx_id', $internalTxId)->first();
        if (!$payment) {
            Log::warning('SePay webhook: payment not found', ['invoice' => $invoice]);
            return;
        }

        if ($payment->status !== Payment::statusPending()) {
            return;
        }

        $isSuccess = in_array($notificationType, ['ORDER_PAID', 'PAYMENT_SUCCESS'], true)
            || in_array($orderStatus, ['captured', 'approved'], true)
            || in_array($txStatus, ['approved'], true)
            || in_array($status, ['success', 'succeeded', 'completed'], true);
        $isFailed = in_array($status, ['failed', 'error', 'canceled'], true)
            || in_array($orderStatus, ['failed', 'cancelled'], true);

        if ($isSuccess) {
            $payment->update([
                'status' => Payment::statusSucceeded(),
                'provider_tx_id' => $providerTxId ?? $payment->provider_tx_id,
                'completed_at' => now(),
            ]);
        } elseif ($isFailed) {
            $payment->update([
                'status' => Payment::statusFailed(),
                'error_code' => $data['error_code'] ?? 'sepay_failed',
                'error_message' => $data['error_message'] ?? 'Thanh toán qua SePay thất bại.',
                'completed_at' => now(),
            ]);
        }

        Log::info('SePay webhook handled', [
            'tx' => $payment->internal_tx_id,
            'status' => $payment->status,
        ]);
    }

    protected function signFields(array $fields, string $secretKey): string
    {
        $order = [
            'merchant',
            'currency',
            'order_amount',
            'operation',
            'order_description',
            'order_invoice_number',
            'customer_id',
            'success_url',
            'error_url',
            'cancel_url',
        ];

        $signed = [];
        foreach ($order as $field) {
            if (!array_key_exists($field, $fields)) {
                continue;
            }
            $signed[] = $field . '=' . $fields[$field];
        }

        return base64_encode(hash_hmac('sha256', implode(',', $signed), $secretKey, true));
    }

    protected function httpClient()
    {
        $client = Http::getFacadeRoot();
        return config('services.payment.disable_tls_verify') ? $client->withoutVerifying() : $client;
    }
}

