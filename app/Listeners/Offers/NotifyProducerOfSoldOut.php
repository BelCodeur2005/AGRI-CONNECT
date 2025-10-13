<?php

// app/Listeners/Offers/NotifyProducerOfSoldOut.php
namespace App\Listeners\Offers;

use App\Events\Offers\OfferSoldOut;
use App\Services\Notifications\NotificationService;
use App\Enums\NotificationType;

class NotifyProducerOfSoldOut
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(OfferSoldOut $event): void
    {
        $offer = $event->offer;
        $producer = $offer->producer->user;

        $this->notificationService->send(
            $producer,
            NotificationType::OFFER_SOLD_OUT,
            'Offre épuisée',
            "Félicitations ! Votre offre '{$offer->title}' est épuisée. Vous avez vendu {$offer->quantity_available}kg.",
            ['offer_id' => $offer->id]
        );
    }
}