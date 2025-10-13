<?php

// app/Listeners/Deliveries/NotifyBuyerDeliveryArrived.php
namespace App\Listeners\Deliveries;

use App\Events\Deliveries\DeliveryArrived;
use App\Services\Notifications\NotificationService;
use App\Enums\NotificationType;

class NotifyBuyerDeliveryArrived
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(DeliveryArrived $event): void
    {
        $delivery = $event->delivery;
        $buyer = $delivery->order->buyer->user;

        $this->notificationService->send(
            $buyer,
            NotificationType::DELIVERY_ARRIVED,
            'Livraison arrivée',
            "Le transporteur est arrivé avec votre commande {$delivery->order->order_number}",
            ['delivery_id' => $delivery->id]
        );
    }
}