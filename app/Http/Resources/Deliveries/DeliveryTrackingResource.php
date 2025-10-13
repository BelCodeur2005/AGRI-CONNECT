<?php

// app/Http/Resources/Deliveries/DeliveryTrackingResource.php
namespace App\Http\Resources\Deliveries;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryTrackingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order->order_number,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'current_location' => $this->current_location,
            'estimated_arrival' => $this->scheduled_delivery_at?->toIso8601String(),
            'transporter' => [
                'name' => $this->transporter->user->name,
                'phone' => $this->transporter->user->phone,
                'vehicle' => "{$this->transporter->vehicle_type} - {$this->transporter->vehicle_registration}",
            ],
            'timeline' => [
                'picked_up' => $this->actual_pickup_at?->toIso8601String(),
                'in_transit' => $this->status->value === 'in_transit',
                'arrived' => $this->status->value === 'arrived',
                'delivered' => $this->actual_delivery_at?->toIso8601String(),
            ],
        ];
    }
}