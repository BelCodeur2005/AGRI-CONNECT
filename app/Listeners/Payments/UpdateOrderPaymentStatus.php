<?php

// app/Listeners/Payments/UpdateOrderPaymentStatus.php
namespace App\Listeners\Payments;

use App\Events\Payments\PaymentHeld;

class UpdateOrderPaymentStatus
{
    public function handle(PaymentHeld $event): void
    {
        $payment = $event->payment;
        
        // Statut déjà mis à jour dans Payment::hold()
        // Ce listener peut servir pour actions additionnelles
        
        $payment->order->update(['status' => 'paid']);
    }
}
