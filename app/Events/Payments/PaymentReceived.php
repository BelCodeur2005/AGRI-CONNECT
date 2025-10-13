<?php

// app/Events/Payments/PaymentReceived.php
namespace App\Events\Payments;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(public Payment $payment) {}
}
