<?php

// app/Http/Resources/Orders/OrderDetailResource.php
namespace App\Http\Resources\Orders;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return array_merge(
            (new OrderResource($this->resource))->toArray($request),
            [
                'payment' => new \App\Http\Resources\Payments\PaymentResource($this->whenLoaded('payment')),
                'delivery' => new \App\Http\Resources\Deliveries\DeliveryResource($this->whenLoaded('delivery')),
                'ratings' => \App\Http\Resources\Ratings\RatingResource::collection($this->whenLoaded('ratings')),
            ]
        );
    }
}