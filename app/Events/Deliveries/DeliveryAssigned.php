<?php

// app/Events/Deliveries/DeliveryAssigned.php
namespace App\Events\Deliveries;

use App\Models\Delivery;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(public Delivery $delivery) {}
}