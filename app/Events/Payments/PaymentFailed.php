<?php

// app/Events/Payments/PaymentFailed.php
namespace App\Events\Payments;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Payment $payment,
        public string $reason
    ) {}
}
