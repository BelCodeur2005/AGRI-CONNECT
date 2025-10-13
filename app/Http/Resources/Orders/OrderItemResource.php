<?php

// app/Http/Resources/Orders/OrderItemResource.php
namespace App\Http\Resources\Orders;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product_name' => $this->product_name,
            'quantity' => (float) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'subtotal' => (float) $this->subtotal,
            'platform_commission' => (float) $this->platform_commission,
            'producer_earnings' => (float) $this->producer_earnings,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'producer' => $this->when($this->relationLoaded('producer'), [
                'id' => $this->producer->id,
                'name' => $this->producer->user->name,
                'farm_name' => $this->producer->farm_name,
            ]),
            'producer_notes' => $this->producer_notes,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'ready_at' => $this->ready_at?->toIso8601String(),
        ];
    }
}
