<?php

// app/Http/Resources/Disputes/DisputeResource.php
namespace App\Http\Resources\Disputes;

use Illuminate\Http\Resources\Json\JsonResource;

class DisputeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order->order_number,
            'type' => [
                'value' => $this->type,
                'label' => ucfirst($this->type),
            ],
            'description' => $this->description,
            'evidence_photo_urls' => $this->evidence_photo_urls,
            'status' => $this->status,
            'reporter' => [
                'name' => $this->reporter->name,
                'role' => $this->reporter->role->label(),
            ],
            'resolution' => $this->resolution,
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}