<?php

// app/Services/Payments/EscrowService.php
namespace App\Services\Payments;

use App\Models\Payment;
use App\Enums\PaymentStatus;

class EscrowService
{
    /**
     * Bloquer paiement en escrow
     */
    public function hold(Payment $payment): void
    {
        $payment->hold();
    }

    /**
     * Libérer paiement
     */
    public function release(Payment $payment): void
    {
        // Libérer chaque split aux producteurs
        foreach ($payment->splits as $split) {
            $this->releaseSplit($split);
        }

        $payment->update([
            'status' => PaymentStatus::RELEASED,
            'released_at' => now(),
        ]);
    }

    /**
     * Libérer un split individuel
     */
    private function releaseSplit($split): void
    {
        // TODO: Intégrer API de transfert vers producteur
        // Pour l'instant, on simule
        
        $split->release();
    }

    /**
     * Vérifier si peut être libéré automatiquement
     */
    public function canAutoRelease(Payment $payment): bool
    {
        // Libération auto après X heures si commande complétée
        $autoReleaseHours = config('agri-connect.payment.escrow_release_delay', 24);
        
        return $payment->status === PaymentStatus::HELD
            && $payment->order->status === \App\Enums\OrderStatus::COMPLETED
            && $payment->time_since_held >= $autoReleaseHours;
    }
}