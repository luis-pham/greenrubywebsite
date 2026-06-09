<?php

namespace App\Payments;

use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function support(array $context);

    public function initPayment(array $context);

    public function confirmPayment(Request $request);

    public function checkTransaction($internalTxId, array $context = []);

    public function handleIpn(Request $request);
}

