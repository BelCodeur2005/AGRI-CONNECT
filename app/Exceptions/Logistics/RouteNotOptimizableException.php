<?php

// app/Exceptions/Logistics/RouteNotOptimizableException.php
namespace App\Exceptions\Logistics;

use App\Exceptions\BaseAgriConnectException;

class RouteNotOptimizableException extends BaseAgriConnectException
{
    protected int $statusCode = 400;
    protected string $errorCode = 'ROUTE_NOT_OPTIMIZABLE';

    public function __construct(string $reason)
    {
        parent::__construct(
            'Impossible d\'optimiser cette tournée',
            ['reason' => $reason]
        );
    }
}