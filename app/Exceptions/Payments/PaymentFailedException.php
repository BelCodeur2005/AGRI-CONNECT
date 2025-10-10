<?php

// app/Exceptions/Payments/PaymentFailedException.php
namespace App\Exceptions\Payments;

use App\Exceptions\BaseAgriConnectException;

class PaymentFailedException extends BaseAgriConnectException
{
    protected int $statusCode = 402;
    protected string $errorCode = 'PAYMENT_FAILED';

    public function __construct(string $reason, array $context = [])
    {
        parent::__construct(
            'Le paiement a échoué: ' . $reason,
            $context
        );
    }
}
