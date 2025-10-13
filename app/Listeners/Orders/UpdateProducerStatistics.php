<?php

// app/Listeners/Orders/UpdateProducerStatistics.php
namespace App\Listeners\Orders;

use App\Events\Orders\OrderCompleted;

class UpdateProducerStatistics
{
    public function handle(OrderCompleted $event): void
    {
        $order = $event->order;

        // Mettre à jour stats de chaque producteur
        foreach ($order->items as $item) {
            if ($item->status->value === 'completed') {
                $producer = $item->producer;
                
                // Stats déjà mises à jour via incrementRevenue()
                // Ce listener peut servir pour calculs additionnels
            }
        }
    }
}