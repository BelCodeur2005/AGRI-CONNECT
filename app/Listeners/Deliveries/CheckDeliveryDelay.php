<?php

// app/Listeners/Deliveries/CheckDeliveryDelay.php
namespace App\Listeners\Deliveries;

use App\Events\Deliveries\DeliveryCompleted;
use App\Services\Notifications\NotificationService;
use App\Enums\NotificationType;

class CheckDeliveryDelay
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(DeliveryCompleted $event): void
    {
        $delivery = $event->delivery;

        // Si retard significatif, notifier admin
        if (!$delivery->on_time && $delivery->delay_minutes > 120) {
            // TODO: Notifier admin
            \Log::warning('Significant delivery delay', [
                'delivery_id' => $delivery->id,
                'delay_minutes' => $delivery->delay_minutes,
            ]);
        }
    }
}
