<?php

// app/Listeners/Payments/CreatePaymentSplits.php
namespace App\Listeners\Payments;

use App\Events\Payments\PaymentReceived;

class CreatePaymentSplits
{
    public function handle(PaymentReceived $event): void
    {
        // Splits déjà créés dans Payment::hold()
        // Ce listener peut servir pour notifications additionnelles
        
        $payment = $event->payment;
        $order = $payment->order;

        // Notifier chaque producteur que le paiement est reçu
        foreach ($payment->splits as $split) {
            $producer = $split->producer;
            app(\App\Services\Notifications\NotificationService::class)
                ->paymentReceived($producer, $payment);
        }
    }
}