<?php

// app/Http/Resources/Catalog/OfferListResource.php
namespace App\Http\Resources\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Profile\ProducerResource;
use App\Http\Resources\Common\LocationResource;

class OfferListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'quantity_available' => (float) $this->quantity_available,
            'remaining_quantity' => (float) $this->remaining_quantity,
            'price_per_unit' => (float) $this->price_per_unit,
            'unit' => $this->product->unit->shortLabel(),
            'available_until' => $this->available_until?->toIso8601String(),
            'organic' => $this->organic,
            'quality_grade' => $this->quality_grade,
            'photos' => $this->photo_urls,
            'product' => new ProductResource($this->whenLoaded('product')),
            'producer' => [
                'id' => $this->producer->id,
                'name' => $this->producer->user->name,
                'farm_name' => $this->producer->farm_name,
                'average_rating' => (float) $this->producer->average_rating,
            ],
            'location' => new LocationResource($this->whenLoaded('location')),
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
        ];
    }
}