<?php

// app/Exceptions/Orders/InsufficientStockException.php
namespace App\Exceptions\Orders;

use App\Exceptions\BaseAgriConnectException;

class InsufficientStockException extends BaseAgriConnectException
{
    protected int $statusCode = 400;
    protected string $errorCode = 'INSUFFICIENT_STOCK';

    public function __construct(int $offerId, float $requested, float $available)
    {
        parent::__construct(
            'Stock insuffisant pour cette offre',
            [
                'offer_id' => $offerId,
                'requested' => $requested,
                'available' => $available,
            ]
        );
    }
}