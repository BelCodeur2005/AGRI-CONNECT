<?php

// app/Events/Deliveries/DeliveryGroupAssigned.php
namespace App\Events\Deliveries;

use App\Models\DeliveryGroup;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryGroupAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(public DeliveryGroup $group) {}
}
