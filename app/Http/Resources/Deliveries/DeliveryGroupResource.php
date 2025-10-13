<?php

// app/Http/Resources/Deliveries/DeliveryGroupResource.php
namespace App\Http\Resources\Deliveries;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryGroupResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'group_number' => $this->group_number,
            'delivery_address' => $this->delivery_address,
            'scheduled_date' => $this->scheduled_date?->toDateString(),
            'scheduled_time_from' => $this->scheduled_time_from?->format('H:i'),
            'scheduled_time_to' => $this->scheduled_time_to?->format('H:i'),
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'total_orders' => $this->total_orders,
            'total_producers' => $this->total_producers,
            'total_weight' => (float) $this->total_weight,
            'total_delivery_cost' => (float) $this->total_delivery_cost,
            'transporter' => $this->when($this->transporter_id, [
                'id' => $this->transporter->id,
                'name' => $this->transporter->user->name,
            ]),
            'deliveries' => DeliveryResource::collection($this->whenLoaded('deliveries')),
        ];
    }
}