<?php

// app/Http/Resources/Cart/CartItemResource.php
namespace App\Http\Resources\Cart;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Catalog\OfferListResource;

class CartItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'quantity' => (float) $this->quantity,
            'subtotal' => (float) $this->subtotal,
            'notes' => $this->notes,
            'is_valid' => $this->is_valid,
            'status_message' => $this->status_message,
            'offer' => new OfferListResource($this->whenLoaded('offer')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
