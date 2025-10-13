<?php

// app/Http/Resources/Profile/TransporterResource.php
namespace App\Http\Resources\Profile;

use Illuminate\Http\Resources\Json\JsonResource;

class TransporterResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'vehicle_type' => $this->vehicle_type,
            'vehicle_registration' => $this->vehicle_registration,
            'max_capacity_kg' => (float) $this->max_capacity_kg,
            'has_refrigeration' => $this->has_refrigeration,
            'service_areas' => $this->service_areas,
            'average_rating' => (float) $this->average_rating,
            'total_ratings' => $this->total_ratings,
            'total_deliveries' => $this->total_deliveries,
            'total_earnings' => (float) $this->total_earnings,
            'bonus_earnings' => (float) $this->bonus_earnings,
            'on_time_rate' => (float) $this->on_time_rate,
            'is_available' => $this->is_available,
            'is_certified' => $this->is_certified,
            'certification_level' => [
                'level' => $this->certification_level->value,
                'label' => $this->certification_level->label(),
                'bonus_percentage' => $this->certification_level->bonusPercentage(),
            ],
        ];
    }
}
