<?php

// app/Listeners/Orders/CreateDeliveryAfterPayment.php
namespace App\Listeners\Orders;

use App\Events\Orders\OrderPaid;
use App\Services\Logistics\DeliveryService;

class CreateDeliveryAfterPayment
{
    public function __construct(
        private DeliveryService $deliveryService
    ) {}

    public function handle(OrderPaid $event): void
    {
        $order = $event->order;

        // Créer livraison si pas déjà existante
        if (!$order->delivery) {
            $this->deliveryService->createForOrder($order);
        }
    }
}