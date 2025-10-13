<?php

// app/Listeners/Orders/ReserveOfferStock.php
namespace App\Listeners\Orders;

use App\Events\Orders\OrderCreated;

class ReserveOfferStock
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        // Stock déjà réservé lors de la création des items
        // Ce listener peut servir pour des actions supplémentaires
    }
}