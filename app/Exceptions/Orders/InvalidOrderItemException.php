<?php

// app/Exceptions/Orders/InvalidOrderItemException.php
namespace App\Exceptions\Orders;

use App\Exceptions\BaseAgriConnectException;

class InvalidOrderItemException extends BaseAgriConnectException
{
    protected int $statusCode = 400;
    protected string $errorCode = 'INVALID_ORDER_ITEM';

    public function __construct(string $reason)
    {
        parent::__construct(
            'Article de commande invalide',
            ['reason' => $reason]
        );
    }
}