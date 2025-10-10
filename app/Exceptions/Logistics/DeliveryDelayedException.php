<?php

// app/Exceptions/Logistics/DeliveryDelayedException.php
namespace App\Exceptions\Logistics;

use App\Exceptions\BaseAgriConnectException;

class DeliveryDelayedException extends BaseAgriConnectException
{
    protected int $statusCode = 200; // Pas une erreur bloquante
    protected string $errorCode = 'DELIVERY_DELAYED';

    public function __construct(int $deliveryId, int $delayMinutes)
    {
        parent::__construct(
            'Livraison retardée',
            [
                'delivery_id' => $deliveryId,
                'delay_minutes' => $delayMinutes,
            ]
        );
    }
}
