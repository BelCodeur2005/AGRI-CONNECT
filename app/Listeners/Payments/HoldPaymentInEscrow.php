<?php

// app/Listeners/Payments/HoldPaymentInEscrow.php
namespace App\Listeners\Payments;

use App\Events\Payments\PaymentReceived;
use App\Services\Payments\EscrowService;

class HoldPaymentInEscrow
{
    public function __construct(
        private EscrowService $escrowService
    ) {}

    public function handle(PaymentReceived $event): void
    {
        // Déjà géré dans PaymentService::confirmPayment()
        // Ce listener peut servir pour logging
        \Log::info('Payment held in escrow', [
            'payment_id' => $event->payment->id,
            'amount' => $event->payment->amount,
        ]);
    }
}
