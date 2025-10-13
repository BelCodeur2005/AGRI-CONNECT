<?php
// app/Listeners/Payments/NotifyBuyerPaymentFailed.php
namespace App\Listeners\Payments;

use App\Events\Payments\PaymentFailed;
use App\Services\Notifications\NotificationService;
use App\Enums\NotificationType;

class NotifyBuyerPaymentFailed
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(PaymentFailed $event): void
    {
        $payment = $event->payment;
        $buyer = $payment->order->buyer->user;

        $this->notificationService->send(
            $buyer,
            NotificationType::PAYMENT_FAILED,
            'Paiement échoué',
            "Le paiement de {$payment->amount} FCFA a échoué. Raison: {$event->reason}",
            ['payment_id' => $payment->id]
        );
    }
}

