<?php

namespace App\Payments\Dto;

class PaymentInitResult
{
    public $internalTxId;
    public $providerTxId;
    public $paymentUrl;

    public function __construct($internalTxId, $providerTxId, $paymentUrl)
    {
        $this->internalTxId = $internalTxId;
        $this->providerTxId = $providerTxId;
        $this->paymentUrl = $paymentUrl;
    }
}

