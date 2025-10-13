<?php

// app/Events/Offers/OfferCreated.php
namespace App\Events\Offers;

use App\Models\Offer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Offer $offer) {}
}