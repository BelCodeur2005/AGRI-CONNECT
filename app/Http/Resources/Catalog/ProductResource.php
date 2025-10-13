<?php

// app/Http/Resources/Catalog/ProductResource.php
namespace App\Http\Resources\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'unit' => [
                'value' => $this->unit->value,
                'label' => $this->unit->label(),
                'short' => $this->unit->shortLabel(),
            ],
            'category' => new CategoryResource($this->whenLoaded('category')),
            'is_perishable' => $this->is_perishable,
            'active_offers_count' => $this->when(
                isset($this->active_offers_count),
                $this->active_offers_count
            ),
        ];
    }
}