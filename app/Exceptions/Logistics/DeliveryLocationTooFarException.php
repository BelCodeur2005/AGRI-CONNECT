<?php

// app/Exceptions/Logistics/DeliveryLocationTooFarException.php
namespace App\Exceptions\Logistics;

use App\Exceptions\BaseAgriConnectException;

class DeliveryLocationTooFarException extends BaseAgriConnectException
{
    protected int $statusCode = 400;
    protected string $errorCode = 'DELIVERY_LOCATION_TOO_FAR';

    public function __construct(float $distance, float $maxDistance)
    {
        parent::__construct(
            'La destination est trop éloignée',
            [
                'distance_km' => $distance,
                'max_distance_km' => $maxDistance,
            ]
        );
    }
}

