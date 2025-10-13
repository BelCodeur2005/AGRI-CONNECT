<?php

// app/Http/Resources/Deliveries/DeliveryResource.php
namespace App\Http\Resources\Deliveries;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Common\LocationResource;

class DeliveryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'pickup_address' => $this->pickup_address,
            'delivery_address' => $this->delivery_address,
            'pickup_location' => new LocationResource($this->whenLoaded('pickupLocation')),
            'delivery_location' => new LocationResource($this->whenLoaded('deliveryLocation')),
            'scheduled_pickup_at' => $this->scheduled_pickup_at?->toIso8601String(),
            'scheduled_delivery_at' => $this->scheduled_delivery_at?->toIso8601String(),
            'actual_pickup_at' => $this->actual_pickup_at?->toIso8601String(),
            'actual_delivery_at' => $this->actual_delivery_at?->toIso8601String(),
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'transporter' => $this->when($this->relationLoaded('transporter'), [
                'id' => $this->transporter->id,
                'name' => $this->transporter->user->name,
                'phone' => $this->transporter->user->phone,
                'vehicle_type' => $this->transporter->vehicle_type,
                'vehicle_registration' => $this->transporter->vehicle_registration,
            ]),
            'current_location' => $this->current_location,
            'delivery_proof_url' => $this->delivery_proof_url,
            'on_time' => $this->on_time,
            'delay_minutes' => $this->delay_minutes,
            'delivery_notes' => $this->delivery_notes,
        ];
    }
}