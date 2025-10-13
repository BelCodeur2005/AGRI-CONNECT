<?php

// app/Events/Deliveries/DeliveryArrived.php
namespace App\Events\Deliveries;

use App\Models\Delivery;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryArrived
{
    use Dispatchable, SerializesModels;

    public function __construct(public Delivery $delivery) {}
}
