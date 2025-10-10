<?php

// app/Exceptions/Logistics/NoTransporterAvailableException.php
namespace App\Exceptions\Logistics;

use App\Exceptions\BaseAgriConnectException;

class NoTransporterAvailableException extends BaseAgriConnectException
{
    protected int $statusCode = 503;
    protected string $errorCode = 'NO_TRANSPORTER_AVAILABLE';

    public function __construct(array $context = [])
    {
        parent::__construct(
            'Aucun transporteur disponible pour cette livraison',
            $context
        );
    }
}
