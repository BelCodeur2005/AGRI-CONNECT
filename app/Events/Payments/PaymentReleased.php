<?php

// app/Events/Payments/PaymentReleased.php
namespace App\Events\Payments;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReleased
{
    use Dispatchable, SerializesModels;

    public function __construct(public Payment $payment) {}
}