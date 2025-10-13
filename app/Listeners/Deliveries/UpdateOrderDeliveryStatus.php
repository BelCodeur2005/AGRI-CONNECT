<?php

// app/Listeners/Deliveries/UpdateOrderDeliveryStatus.php
namespace App\Listeners\Deliveries;

use App\Events\Deliveries\DeliveryCompleted;

class UpdateOrderDeliveryStatus
{
    public function handle(DeliveryCompleted $event): void
    {
        // Statut déjà mis à jour dans DeliveryService::complete()
        // Ce listener peut servir pour actions additionnelles
    }
}