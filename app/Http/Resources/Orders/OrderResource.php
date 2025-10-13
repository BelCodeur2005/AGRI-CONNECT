<?php

// app/Http/Resources/Orders/OrderResource.php
namespace App\Http\Resources\Orders;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'subtotal' => (float) $this->subtotal,
            'platform_commission' => (float) $this->platform_commission,
            'delivery_cost' => (float) $this->delivery_cost,
            'total_amount' => (float) $this->total_amount,
            'delivery_address' => $this->delivery_address,
            'delivery_notes' => $this->delivery_notes,
            'total_items' => $this->total_items,
            'items_confirmed' => $this->items_confirmed,
            'items_cancelled' => $this->items_cancelled,
            'is_multi_producer' => $this->is_multi_producer,
            'items_progress' => $this->items_progress,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status_label,
                'color' => $this->status_color,
            ],
            'buyer' => $this->when($this->relationLoaded('buyer'), [
                'id' => $this->buyer->id,
                'business_name' => $this->buyer->business_name,
                'name' => $this->buyer->user->name,
            ]),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'delivery_location' => new \App\Http\Resources\Common\LocationResource($this->whenLoaded('deliveryLocation')),
            'can_be_cancelled' => $this->canBeCancelled(),
            'can_be_rated' => $this->canBeRated(),
            'created_at' => $this->created_at?->toIso8601String(),
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}