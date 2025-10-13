<?php

// app/Events/Offers/OfferExpired.php
namespace App\Events\Offers;

use App\Models\Offer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferExpired
{
    use Dispatchable, SerializesModels;

    public function __construct(public Offer $offer) {}
}