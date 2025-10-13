<?php

// app/Http/Resources/Catalog/OfferDetailResource.php
namespace App\Http\Resources\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;

class OfferDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'quantity_available' => (float) $this->quantity_available,
            'quantity_reserved' => (float) $this->quantity_reserved,
            'remaining_quantity' => (float) $this->remaining_quantity,
            'min_order_quantity' => (float) $this->min_order_quantity,
            'price_per_unit' => (float) $this->price_per_unit,
            'harvest_date' => $this->harvest_date?->toDateString(),
            'available_from' => $this->available_from?->toDateString(),
            'available_until' => $this->available_until?->toDateString(),
            'photos' => $this->photo_urls,
            'quality_grade' => $this->quality_grade,
            'organic' => $this->organic,
            'views_count' => $this->views_count,
            'product' => new ProductResource($this->whenLoaded('product')),
            'producer' => new \App\Http\Resources\Profile\ProducerResource($this->whenLoaded('producer')),
            'location' => new \App\Http\Resources\Common\LocationResource($this->whenLoaded('location')),
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'can_order' => $this->canOrder(1),
        ];
    }
}

