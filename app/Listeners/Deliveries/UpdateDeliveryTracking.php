<?php

// app/Listeners/Deliveries/UpdateDeliveryTracking.php
namespace App\Listeners\Deliveries;

use App\Events\Deliveries\DeliveryInTransit;
use Illuminate\Support\Facades\Log;

class UpdateDeliveryTracking
{
    public function handle(DeliveryInTransit $event): void
    {
        $delivery = $event->delivery;

        // Log pour tracking
        Log::info('Delivery in transit', [
            'delivery_id' => $delivery->id,
            'order_number' => $delivery->order->order_number,
        ]);

        // Ici on pourrait intégrer un système de tracking temps réel
    }
}