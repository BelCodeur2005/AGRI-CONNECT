<?php

// app/Exceptions/Payments/InsufficientBalanceException.php
namespace App\Exceptions\Payments;

use App\Exceptions\BaseAgriConnectException;

class InsufficientBalanceException extends BaseAgriConnectException
{
    protected int $statusCode = 402;
    protected string $errorCode = 'INSUFFICIENT_BALANCE';

    public function __construct(float $required, float $available)
    {
        parent::__construct(
            'Solde insuffisant pour effectuer le paiement',
            [
                'required' => $required,
                'available' => $available,
            ]
        );
    }
}
