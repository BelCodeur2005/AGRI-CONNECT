<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Profile\ProducerResource;
use App\Http\Resources\Common\LocationResource;

class OfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            
            // Product Information
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'description' => $this->product->description,
                'category' => [
                    'id' => $this->product->category->id,
                    'name' => $this->product->category->name,
                    'slug' => $this->product->category->slug,
                ],
                'unit' => $this->product->unit,
                'image_url' => $this->product->image_url,
            ],
            
            // Producer Information
            'producer' => new ProducerResource($this->whenLoaded('producer')),
            
            // Offer Details
            'quantity_available' => $this->quantity_available,
            'unit_price' => [
                'amount' => $this->unit_price,
                'formatted' => number_format($this->unit_price, 0, ',', ' ') . ' FCFA',
            ],
            'total_value' => [
                'amount' => $this->quantity_available * $this->unit_price,
                'formatted' => number_format($this->quantity_available * $this->unit_price, 0, ',', ' ') . ' FCFA',
            ],
            'minimum_order' => $this->minimum_order,
            
            // Location
            'location' => new LocationResource($this->whenLoaded('location')),
            'pickup_address' => $this->pickup_address,
            
            // Dates
            'harvest_date' => $this->harvest_date?->format('Y-m-d'),
            'available_from' => $this->available_from?->format('Y-m-d'),
            'available_until' => $this->available_until?->format('Y-m-d'),
            'expires_in_days' => $this->available_until ? now()->diffInDays($this->available_until, false) : null,
            
            // Quality & Certification
            'quality_grade' => $this->quality_grade,
            'is_organic' => $this->is_organic,
            'certifications' => $this->certifications,
            
            // Status
            'status' => $this->status,
            'is_active' => $this->is_active,
            'is_available' => $this->is_active && $this->quantity_available > 0 && (!$this->available_until || $this->available_until->isFuture()),
            
            // Statistics (when loaded)
            'views_count' => $this->when(isset($this->views_count), $this->views_count ?? 0),
            'orders_count' => $this->when(isset($this->orders_count), $this->orders_count ?? 0),
            'favorites_count' => $this->when(isset($this->favorites_count), $this->favorites_count ?? 0),
            'average_rating' => $this->when(isset($this->average_rating), $this->average_rating),
            
            // User interactions (when authenticated)
            'is_favorited' => $this->when(
                $request->user(), 
                fn() => $this->favorites()->where('user_id', $request->user()?->id)->exists()
            ),
            
            // Metadata
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
