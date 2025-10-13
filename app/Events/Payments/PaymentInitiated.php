<?php

// app/Events/Payments/PaymentInitiated.php
namespace App\Events\Payments;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentInitiated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Payment $payment) {}
}