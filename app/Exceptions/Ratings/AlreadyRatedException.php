<?php

// app/Exceptions/Ratings/AlreadyRatedException.php
namespace App\Exceptions\Ratings;

use App\Exceptions\BaseAgriConnectException;

class AlreadyRatedException extends BaseAgriConnectException
{
    protected int $statusCode = 400;
    protected string $errorCode = 'ALREADY_RATED';

    public function __construct(int $orderId)
    {
        parent::__construct(
            'Vous avez déjà évalué cette commande',
            ['order_id' => $orderId]
        );
    }
}

