<?php

// app/Listeners/Deliveries/NotifyBuyerDeliveryCompleted.php
namespace App\Listeners\Deliveries;

use App\Events\Deliveries\DeliveryCompleted;
use App\Services\Notifications\NotificationService;

class NotifyBuyerDeliveryCompleted
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(DeliveryCompleted $event): void
    {
        $delivery = $event->delivery;
        $buyer = $delivery->order->buyer->user;

        $this->notificationService->deliveryCompleted($buyer, $delivery);
    }
}