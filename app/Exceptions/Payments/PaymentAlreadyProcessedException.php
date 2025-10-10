<?php

// app/Exceptions/Payments/PaymentAlreadyProcessedException.php
namespace App\Exceptions\Payments;

use App\Exceptions\BaseAgriConnectException;

class PaymentAlreadyProcessedException extends BaseAgriConnectException
{
    protected int $statusCode = 400;
    protected string $errorCode = 'PAYMENT_ALREADY_PROCESSED';

    public function __construct(string $transactionId)
    {
        parent::__construct(
            'Ce paiement a déjà été traité',
            ['transaction_id' => $transactionId]
        );
    }
}