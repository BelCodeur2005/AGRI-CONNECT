<?php

// app/Listeners/Orders/NotifyBuyerItemConfirmed.php
namespace App\Listeners\Orders;

use App\Events\Orders\OrderItemConfirmed;
use App\Services\Notifications\NotificationService;
use App\Enums\NotificationType;

class NotifyBuyerItemConfirmed
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(OrderItemConfirmed $event): void
    {
        $item = $event->item;
        $buyer = $item->order->buyer->user;

        $this->notificationService->send(
            $buyer,
            NotificationType::ITEM_CONFIRMED,
            'Article confirmé',
            "Le producteur a confirmé votre commande de {$item->quantity}kg de {$item->product_name}",
            ['item_id' => $item->id, 'order_id' => $item->order_id]
        );

        // Vérifier si tous items confirmés
        $allConfirmed = $item->order->items()
            ->whereNotIn('status', ['cancelled'])
            ->where('status', '!=', 'confirmed')
            ->count() === 0;

        if ($allConfirmed) {
            event(new \App\Events\Orders\OrderFullyConfirmed($item->order));
        }
    }
}