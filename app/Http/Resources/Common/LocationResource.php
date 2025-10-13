<?php
// app/Http/Resources/Common/LocationResource.php
namespace App\Http\Resources\Common;

use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'region' => $this->region,
            'full_name' => $this->full_name,
            'type' => $this->type,
            'coordinates' => $this->when($this->latitude && $this->longitude, [
                'latitude' => (float) $this->latitude,
                'longitude' => (float) $this->longitude,
            ]),
        ];
    }
}
