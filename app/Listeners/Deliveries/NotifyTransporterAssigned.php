<?php

// app/Listeners/Deliveries/NotifyTransporterAssigned.php
namespace App\Listeners\Deliveries;

use App\Events\Deliveries\DeliveryGroupAssigned;
use App\Services\Notifications\NotificationService;
use App\Enums\NotificationType;

class NotifyTransporterAssigned
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(DeliveryGroupAssigned $event): void
    {
        $group = $event->group;
        $transporter = $group->transporter->user;

        $this->notificationService->send(
            $transporter,
            NotificationType::DELIVERY_ASSIGNED,
            'Nouvelle tournée',
            "Une tournée de {$group->total_orders} livraisons vous a été assignée pour le {$group->scheduled_date->format('d/m/Y')}",
            ['group_id' => $group->id]
        );
    }
}