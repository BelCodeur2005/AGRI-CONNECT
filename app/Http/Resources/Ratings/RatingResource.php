<?php

// app/Http/Resources/Ratings/RatingResource.php
namespace App\Http\Resources\Ratings;

use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'overall_score' => $this->overall_score,
            'quality_score' => $this->quality_score,
            'punctuality_score' => $this->punctuality_score,
            'communication_score' => $this->communication_score,
            'packaging_score' => $this->packaging_score,
            'comment' => $this->comment,
            'rater' => [
                'name' => $this->rater->name,
                'role' => $this->rater->role->label(),
            ],
            'is_verified' => $this->is_verified,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
