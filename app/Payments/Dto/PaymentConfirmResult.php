<?php

namespace App\Payments\Dto;

class PaymentConfirmResult
{
    public $internalTxId;
    public $status;
    public $providerTxId;
    public $message;

    public function __construct($internalTxId, $status, $providerTxId = null, $message = null)
    {
        $this->internalTxId = $internalTxId;
        $this->status = $status;
        $this->providerTxId = $providerTxId;
        $this->message = $message;
    }
}

