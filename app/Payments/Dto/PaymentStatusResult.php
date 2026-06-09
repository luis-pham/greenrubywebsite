<?php

namespace App\Payments\Dto;

class PaymentStatusResult
{
    public $internalTxId;
    public $status;
    public $providerTxId;
    public $raw;

    public function __construct($internalTxId, $status, $providerTxId = null, $raw = null)
    {
        $this->internalTxId = $internalTxId;
        $this->status = $status;
        $this->providerTxId = $providerTxId;
        $this->raw = $raw;
    }
}

