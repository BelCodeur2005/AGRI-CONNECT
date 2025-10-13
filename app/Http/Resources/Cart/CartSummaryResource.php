<?php

// app/Http/Resources/Cart/CartSummaryResource.php
namespace App\Http\Resources\Cart;

use Illuminate\Http\Resources\Json\JsonResource;

class CartSummaryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'valid_items' => CartItemResource::collection($this['valid_items']),
            'invalid_items' => CartItemResource::collection($this['invalid_items']),
            'summary' => [
                'subtotal' => (float) $this['summary']['subtotal'],
                'estimated_delivery_cost' => (float) $this['summary']['estimated_delivery_cost'],
                'estimated_total' => (float) $this['summary']['estimated_total'],
                'total_items' => $this['summary']['total_items'],
                'unique_producers' => $this['summary']['unique_producers'],
                'is_multi_producer' => $this['summary']['is_multi_producer'],
            ],
        ];
    }
}