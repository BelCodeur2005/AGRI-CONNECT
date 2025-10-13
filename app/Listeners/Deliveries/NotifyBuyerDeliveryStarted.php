<?php

// app/Listeners/Deliveries/NotifyBuyerDeliveryStarted.php
namespace App\Listeners\Deliveries;

use App\Events\Deliveries\DeliveryStarted;
use App\Services\Notifications\NotificationService;

class NotifyBuyerDeliveryStarted
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(DeliveryStarted $event): void
    {
        $delivery = $event->delivery;
        $buyer = $delivery->order->buyer->user;

        $this->notificationService->deliveryStarted($buyer, $delivery);
    }
}
