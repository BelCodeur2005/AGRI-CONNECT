<?php

// app/Services/Notifications/NotificationService.php
namespace App\Services\Notifications;

use App\Models\User;
use App\Models\Notification;
use App\Enums\NotificationType;

class NotificationService
{
    public function __construct(
        private SmsService $smsService,
        private PushNotificationService $pushService,
        private EmailService $emailService
    ) {}

    /**
     * Envoyer notification complète
     */
    public function send(
        User $user,
        NotificationType $type,
        string $title,
        string $body,
        array $data = []
    ): Notification {
        // Créer notification en base
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type->value,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);

        // Envoyer SMS si priorité haute
        if ($type->shouldSendSMS()) {
            $this->smsService->send($user->phone, $body);
        }

        // Envoyer push notification
        if ($type->shouldSendPush() && $user->fcm_token) {
            $this->pushService->send($user->fcm_token, $title, $body, $data);
        }

        // Envoyer email si disponible
        if ($user->email && $type->priority() === 'high') {
            $this->emailService->send($user->email, $title, $body);
        }

        $notification->update(['is_sent' => true]);

        return $notification;
    }

    /**
     * Notifications prédéfinies
     */
    public function orderCreated(User $producer, $order): void
    {
        $itemsForProducer = $order->items->where('producer_id', $producer->id);
        $totalQuantity = $itemsForProducer->sum('quantity');

        $this->send(
            $producer->user,
            NotificationType::ORDER_CREATED,
            'Nouvelle commande',
            "Vous avez reçu une commande de {$totalQuantity}kg. Commande #{$order->order_number}",
            ['order_id' => $order->id]
        );
    }

    public function orderConfirmed(User $buyer, $order): void
    {
        $this->send(
            $buyer,
            NotificationType::ORDER_CONFIRMED,
            'Commande confirmée',
            "Votre commande #{$order->order_number} a été confirmée par les producteurs.",
            ['order_id' => $order->id]
        );
    }

    public function paymentReceived(User $producer, $payment): void
    {
        $this->send(
            $producer->user,
            NotificationType::PAYMENT_RECEIVED,
            'Paiement reçu',
            "Le paiement de {$payment->amount} FCFA a été reçu. Il sera libéré après livraison.",
            ['payment_id' => $payment->id]
        );
    }

    public function deliveryStarted(User $buyer, $delivery): void
    {
        $this->send(
            $buyer,
            NotificationType::DELIVERY_STARTED,
            'Livraison en cours',
            "Votre commande #{$delivery->order->order_number} est en route !",
            ['delivery_id' => $delivery->id]
        );
    }

    public function deliveryCompleted(User $buyer, $delivery): void
    {
        $this->send(
            $buyer,
            NotificationType::DELIVERY_COMPLETED,
            'Livraison effectuée',
            "Votre commande a été livrée. Veuillez confirmer la réception.",
            ['delivery_id' => $delivery->id]
        );
    }

    public function paymentReleased(User $producer, $split): void
    {
        $this->send(
            $producer->user,
            NotificationType::PAYMENT_RELEASED,
            'Paiement libéré',
            "Un paiement de {$split->net_amount} FCFA a été transféré sur votre compte.",
            ['split_id' => $split->id]
        );
    }
}
