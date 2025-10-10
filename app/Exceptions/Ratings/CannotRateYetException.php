<?php

// app/Exceptions/Ratings/CannotRateYetException.php
namespace App\Exceptions\Ratings;

use App\Exceptions\BaseAgriConnectException;

class CannotRateYetException extends BaseAgriConnectException
{
    protected int $statusCode = 400;
    protected string $errorCode = 'CANNOT_RATE_YET';

    public function __construct(string $reason)
    {
        parent::__construct(
            'Vous ne pouvez pas encore évaluer',
            ['reason' => $reason]
        );
    }
}