<?php

// app/Listeners/Offers/NotifyProducerOfExpiredOffer.php
namespace App\Listeners\Offers;

use App\Events\Offers\OfferExpired;
use App\Services\Notifications\NotificationService;
use App\Enums\NotificationType;

class NotifyProducerOfExpiredOffer
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(OfferExpired $event): void
    {
        $offer = $event->offer;
        $producer = $offer->producer->user;

        $this->notificationService->send(
            $producer,
            NotificationType::OFFER_EXPIRING,
            'Offre expirée',
            "Votre offre '{$offer->title}' a expiré. Vous pouvez la prolonger ou créer une nouvelle offre.",
            ['offer_id' => $offer->id]
        );
    }
}
