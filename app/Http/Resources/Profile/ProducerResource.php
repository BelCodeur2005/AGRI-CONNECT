<?php

// app/Http/Resources/Profile/ProducerResource.php
namespace App\Http\Resources\Profile;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Common\LocationResource;

class ProducerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'farm_name' => $this->farm_name,
            'farm_size' => $this->farm_size,
            'farm_address' => $this->farm_address,
            'years_experience' => $this->years_experience,
            'certifications' => $this->certifications,
            'location' => new LocationResource($this->whenLoaded('location')),
            'average_rating' => (float) $this->average_rating,
            'total_ratings' => $this->total_ratings,
            'total_orders' => $this->total_orders,
            'total_revenue' => (float) $this->total_revenue,
            'pending_earnings' => (float) $this->pending_earnings,
            'verification_status' => $this->verification_status?->value,
            'can_create_offers' => $this->canCreateOffers(),
        ];
    }
}