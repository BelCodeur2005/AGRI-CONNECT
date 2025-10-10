<?php

// app/Exceptions/Orders/EmptyCartException.php
namespace App\Exceptions\Orders;

use App\Exceptions\BaseAgriConnectException;

class EmptyCartException extends BaseAgriConnectException
{
    protected int $statusCode = 400;
    protected string $errorCode = 'EMPTY_CART';

    public function __construct()
    {
        parent::__construct('Votre panier est vide');
    }
}
