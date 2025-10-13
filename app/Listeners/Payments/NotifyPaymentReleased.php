<?php

// app/Listeners/Payments/NotifyPaymentReleased.php
namespace App\Listeners\Payments;

use App\Events\Payments\PaymentReleased;
use App\Services\Notifications\NotificationService;

class NotifyPaymentReleased
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(PaymentReleased $event): void
    {
        $payment = $event->payment;

        // Notifier chaque producteur
        foreach ($payment->splits as $split) {
            $this->notificationService->paymentReleased($split->producer, $split);
        }
    }
}
