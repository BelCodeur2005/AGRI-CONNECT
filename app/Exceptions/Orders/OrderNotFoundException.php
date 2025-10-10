<?php

// app/Exceptions/Orders/OrderNotFoundException.php
namespace App\Exceptions\Orders;

use App\Exceptions\BaseAgriConnectException;

class OrderNotFoundException extends BaseAgriConnectException
{
    protected int $statusCode = 404;
    protected string $errorCode = 'ORDER_NOT_FOUND';

    public function __construct(string $orderId)
    {
        parent::__construct(
            'Commande introuvable',
            ['order_id' => $orderId]
        );
    }
}
