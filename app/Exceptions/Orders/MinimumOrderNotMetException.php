<?php

// app/Exceptions/Orders/MinimumOrderNotMetException.php
namespace App\Exceptions\Orders;

use App\Exceptions\BaseAgriConnectException;

class MinimumOrderNotMetException extends BaseAgriConnectException
{
    protected int $statusCode = 400;
    protected string $errorCode = 'MINIMUM_ORDER_NOT_MET';

    public function __construct(float $minimum, float $current)
    {
        parent::__construct(
            'Quantité minimale de commande non atteinte',
            [
                'minimum_required' => $minimum,
                'current_quantity' => $current,
            ]
        );
    }
}
