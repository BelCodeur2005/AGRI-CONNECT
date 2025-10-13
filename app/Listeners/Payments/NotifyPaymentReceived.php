<?php

// app/Listeners/Payments/NotifyPaymentReceived.php
namespace App\Listeners\Payments;

use App\Events\Payments\PaymentReceived;
use App\Services\Notifications\NotificationService;
use App\Enums\NotificationType;

class NotifyPaymentReceived
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;
        $buyer = $payment->order->buyer->user;

        $this->notificationService->send(
            $buyer,
            NotificationType::PAYMENT_RECEIVED,
            'Paiement confirmé',
            "Votre paiement de {$payment->amount} FCFA a été reçu avec succès pour la commande {$payment->order->order_number}",
            ['payment_id' => $payment->id]
        );
    }
}