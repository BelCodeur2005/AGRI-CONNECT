<?php

// app/Listeners/Offers/LogOfferCreation.php
namespace App\Listeners\Offers;

use App\Events\Offers\OfferCreated;
use Illuminate\Support\Facades\Log;

class LogOfferCreation
{
    public function handle(OfferCreated $event): void
    {
        $offer = $event->offer;

        Log::info('New offer created', [
            'offer_id' => $offer->id,
            'producer_id' => $offer->producer_id,
            'product_id' => $offer->product_id,
            'quantity' => $offer->quantity_available,
            'price' => $offer->price_per_unit,
        ]);
    }
}