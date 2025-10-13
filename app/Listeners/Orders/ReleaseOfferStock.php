<?php

// app/Listeners/Orders/ReleaseOfferStock.php
namespace App\Listeners\Orders;

use App\Events\Orders\OrderCancelled;

class ReleaseOfferStock
{
    public function handle(OrderCancelled $event): void
    {
        // Stock déjà libéré dans Order::cancel()
        // Ce listener peut servir pour logging ou autres actions
    }
}