<?php

// app/Listeners/Payments/LogPaymentInitiation.php
namespace App\Listeners\Payments;

use App\Events\Payments\PaymentInitiated;
use Illuminate\Support\Facades\Log;

class LogPaymentInitiation
{
    public function handle(PaymentInitiated $event): void
    {
        Log::info('Payment initiated', [
            'payment_id' => $event->payment->id,
            'order_id' => $event->payment->order_id,
            'amount' => $event->payment->amount,
            'method' => $event->payment->method->value,
        ]);
    }
}

