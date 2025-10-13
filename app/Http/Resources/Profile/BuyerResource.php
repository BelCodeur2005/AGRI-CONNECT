<?php

// app/Http/Resources/Profile/BuyerResource.php
namespace App\Http\Resources\Profile;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Common\LocationResource;

class BuyerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'business_name' => $this->business_name,
            'business_type' => $this->business_type,
            'delivery_address' => $this->delivery_address,
            'stars_rating' => $this->stars_rating,
            'location' => new LocationResource($this->whenLoaded('location')),
            'average_rating' => (float) $this->average_rating,
            'total_ratings' => $this->total_ratings,
            'total_orders' => $this->total_orders,
            'total_spent' => (float) $this->total_spent,
            'average_order_value' => (float) $this->average_order_value,
            'monthly_spending' => (float) $this->monthly_spending,
            'cart_summary' => $this->when(
                $request->routeIs('profile.*'),
                $this->cart_summary
            ),
        ];
    }
}