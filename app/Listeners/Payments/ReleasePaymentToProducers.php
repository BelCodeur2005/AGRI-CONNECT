<?php

// app/Listeners/Payments/ReleasePaymentToProducers.php
namespace App\Listeners\Payments;

use App\Events\Orders\OrderCompleted;
use App\Services\Payments\PaymentService;

class ReleasePaymentToProducers
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function handle(OrderCompleted $event): void
    {
        $order = $event->order;
        $payment = $order->payment;

        if ($payment && $payment->canBeReleased()) {
            $this->paymentService->releaseToProducers($payment);
        }
    }
}