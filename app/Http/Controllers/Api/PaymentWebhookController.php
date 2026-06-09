<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Payments\PaymentManager;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class PaymentWebhookController extends Controller
{
    protected $payments;

    public function __construct(PaymentManager $payments)
    {
        $this->payments = $payments;
    }

    public function stripe(Request $request)
    {
        return $this->handleWebhook('stripe', $request);
    }

    public function paypal(Request $request)
    {
        return $this->handleWebhook('paypal', $request);
    }

    public function sepay(Request $request)
    {
        return $this->handleWebhook('sepay', $request);
    }

    protected function handleWebhook(string $method, Request $request)
    {
        try {
            $this->payments->handleIpn($method, $request);
            return response()->json(['status' => 'ok']);
        } catch (HttpExceptionInterface $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error($method . ' webhook error', ['exception' => $e]);
            return response()->json(['status' => 'error'], 500);
        }
    }
}

