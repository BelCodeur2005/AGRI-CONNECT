<?php

// app/Listeners/Orders/NotifyBuyerOrderConfirmed.php
namespace App\Listeners\Orders;

use App\Events\Orders\OrderFullyConfirmed;
use App\Services\Notifications\NotificationService;

class NotifyBuyerOrderConfirmed
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(OrderFullyConfirmed $event): void
    {
        $order = $event->order;
        $buyer = $order->buyer->user;

        $this->notificationService->orderConfirmed($buyer, $order);
    }
}