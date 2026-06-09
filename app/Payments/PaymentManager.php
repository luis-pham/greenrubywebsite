<?php

namespace App\Payments;

use Illuminate\Http\Request;

class PaymentManager
{
    protected function gateway(string $method): PaymentGatewayInterface
    {
        $gateways = config('payment.gateways', []);
        if (!isset($gateways[$method])) {
            throw new \RuntimeException('No supported payment gateway for method: ' . $method);
        }
        $gateway = app($gateways[$method]);
        if (!$gateway->support(['method' => $method])) {
            throw new \RuntimeException('Payment gateway does not support method: ' . $method);
        }
        return $gateway;
    }

    protected function resolve(array $context): PaymentGatewayInterface
    {
        $method = $context['method'] ?? null;
        if (!$method) {
            throw new \RuntimeException('Payment method is required.');
        }
        return $this->gateway($method);
    }

    public function init(array $context)
    {
        return $this->resolve($context)->initPayment($context);
    }

    public function confirm(string $method, Request $request)
    {
        return $this->resolve(['method' => $method])->confirmPayment($request);
    }

    public function check(string $method, $internalTxId, array $context = [])
    {
        $context['method'] = $method;
        return $this->resolve($context)->checkTransaction($internalTxId, $context);
    }

    public function handleIpn(string $method, Request $request): void
    {
        $this->resolve(['method' => $method])->handleIpn($request);
    }
}

