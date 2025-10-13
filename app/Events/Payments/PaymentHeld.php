<?php
// app/Events/Payments/PaymentHeld.php
namespace App\Events\Payments;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentHeld
{
    use Dispatchable, SerializesModels;

    public function __construct(public Payment $payment) {}
}