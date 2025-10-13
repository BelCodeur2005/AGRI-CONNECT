<?php

// app/Http/Resources/Catalog/CategoryResource.php
namespace App\Http\Resources\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'products_count' => $this->when(
                isset($this->products_count),
                $this->products_count
            ),
        ];
    }
}