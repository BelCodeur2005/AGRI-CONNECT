<?php

// app/Events/Deliveries/DeliveryInTransit.php
namespace App\Events\Deliveries;

use App\Models\Delivery;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryInTransit
{
    use Dispatchable, SerializesModels;

    public function __construct(public Delivery $delivery) {}
}