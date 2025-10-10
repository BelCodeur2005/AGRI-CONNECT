<?php

// app/Exceptions/Orders/OrderCannotBeCancelledException.php
namespace App\Exceptions\Orders;

use App\Exceptions\BaseAgriConnectException;

class OrderCannotBeCancelledException extends BaseAgriConnectException
{
    protected int $statusCode = 400;
    protected string $errorCode = 'ORDER_CANNOT_BE_CANCELLED';

    public function __construct(string $currentStatus)
    {
        parent::__construct(
            'Cette commande ne peut plus être annulée',
            ['current_status' => $currentStatus]
        );
    }
}