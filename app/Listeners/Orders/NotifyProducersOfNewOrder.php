<?php

// app/Listeners/Orders/NotifyProducersOfNewOrder.php
namespace App\Listeners\Orders;

use App\Events\Orders\OrderCreated;
use App\Services\Notifications\NotificationService;

class NotifyProducersOfNewOrder
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        // Notifier chaque producteur concerné
        $producerIds = $order->items->pluck('producer_id')->unique();

        foreach ($producerIds as $producerId) {
            $producer = \App\Models\Producer::find($producerId);
            if ($producer) {
                $this->notificationService->orderCreated($producer, $order);
            }
        }
    }
}