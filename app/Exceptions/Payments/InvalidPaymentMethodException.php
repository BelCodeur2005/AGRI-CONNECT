<?php

// app/Exceptions/Payments/InvalidPaymentMethodException.php
namespace App\Exceptions\Payments;

use App\Exceptions\BaseAgriConnectException;

class InvalidPaymentMethodException extends BaseAgriConnectException
{
    protected int $statusCode = 400;
    protected string $errorCode = 'INVALID_PAYMENT_METHOD';

    public function __construct(string $method)
    {
        parent::__construct(
            'Méthode de paiement invalide',
            ['method' => $method]
        );
    }
}
