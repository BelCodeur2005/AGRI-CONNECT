<?php

// app/Exceptions/Catalog/OfferNotActiveException.php
namespace App\Exceptions\Catalog;

use App\Exceptions\BaseAgriConnectException;

class OfferNotActiveException extends BaseAgriConnectException
{
    protected int $statusCode = 400;
    protected string $errorCode = 'OFFER_NOT_ACTIVE';

    public function __construct(int $offerId, string $currentStatus)
    {
        parent::__construct(
            'Cette offre n\'est plus active',
            [
                'offer_id' => $offerId,
                'status' => $currentStatus,
            ]
        );
    }
}
