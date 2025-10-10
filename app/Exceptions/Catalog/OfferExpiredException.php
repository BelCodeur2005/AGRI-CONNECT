<?php

// app/Exceptions/Catalog/OfferExpiredException.php
namespace App\Exceptions\Catalog;

use App\Exceptions\BaseAgriConnectException;

class OfferExpiredException extends BaseAgriConnectException
{
    protected int $statusCode = 410;
    protected string $errorCode = 'OFFER_EXPIRED';

    public function __construct(int $offerId)
    {
        parent::__construct(
            'Cette offre a expiré',
            ['offer_id' => $offerId]
        );
    }
}
