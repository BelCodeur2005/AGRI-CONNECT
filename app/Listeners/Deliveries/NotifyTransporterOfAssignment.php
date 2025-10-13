<?php

// app/Listeners/Deliveries/NotifyTransporterOfAssignment.php
namespace App\Listeners\Deliveries;

use App\Events\Deliveries\DeliveryAssigned;
use App\Services\Notifications\NotificationService;
use App\Enums\NotificationType;

class NotifyTransporterOfAssignment
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(DeliveryAssigned $event): void
    {
        $delivery = $event->delivery;
        $transporter = $delivery->transporter->user;

        $this->notificationService->send(
            $transporter,
            NotificationType::DELIVERY_ASSIGNED,
            'Nouvelle livraison',
            "Une livraison vous a été assignée. Collecte à {$delivery->pickup_address}",
            ['delivery_id' => $delivery->id]
        );
    }
}