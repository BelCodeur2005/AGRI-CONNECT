<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Payments\PaymentResource;
use App\Http\Resources\Deliveries\DeliveryResource;
use App\Http\Resources\Common\LocationResource;
use App\Traits\HasPriceCalculation;

class BuyerOrderResource extends JsonResource
{
    use HasPriceCalculation;

    /**
     * Transform the resource into an array - Vue Acheteur
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            
            // Order Summary
            'summary' => [
                'total_items' => $this->total_items,
                'items_confirmed' => $this->items_confirmed,
                'items_cancelled' => $this->items_cancelled,
                'items_pending' => $this->total_items - $this->items_confirmed - $this->items_cancelled,
                'total_producers' => $this->items->pluck('producer_id')->unique()->count(),
                'is_multi_producer' => $this->is_multi_producer,
            ],
            
            // Financial Summary
            'financial' => [
                'subtotal' => [
                    'amount' => $this->subtotal,
                    'formatted' => $this->formatPrice($this->subtotal),
                ],
                'platform_commission' => [
                    'amount' => $this->platform_commission,
                    'formatted' => $this->formatPrice($this->platform_commission),
                ],
                'delivery_cost' => [
                    'amount' => $this->delivery_cost,
                    'formatted' => $this->formatPrice($this->delivery_cost),
                ],
                'total_amount' => [
                    'amount' => $this->total_amount,
                    'formatted' => $this->formatPrice($this->total_amount),
                ],
            ],
            
            // Items grouped by producer
            'items_by_producer' => $this->items->groupBy('producer_id')->map(function ($items, $producerId) {
                $firstItem = $items->first();
                return [
                    'producer' => [
                        'id' => $firstItem->producer->id,
                        'name' => $firstItem->producer->user->name,
                        'farm_name' => $firstItem->producer->farm_name,
                        'phone' => $firstItem->producer->user->phone,
                        'average_rating' => (float) $firstItem->producer->average_rating,
                    ],
                    'items' => OrderItemResource::collection($items),
                    'subtotal' => [
                        'amount' => $items->sum('subtotal'),
                        'formatted' => $this->formatPrice($items->sum('subtotal')),
                    ],
                    'status_summary' => [
                        'confirmed' => $items->where('status.value', 'confirmed')->count(),
                        'pending' => $items->where('status.value', 'pending')->count(),
                        'cancelled' => $items->where('status.value', 'cancelled')->count(),
                    ],
                ];
            })->values(),
            
            // Delivery Information
            'delivery' => [
                'address' => $this->delivery_address,
                'location' => new LocationResource($this->whenLoaded('deliveryLocation')),
                'notes' => $this->delivery_notes,
                'requested_date' => $this->requested_delivery_date?->format('Y-m-d H:i'),
            ],
            
            // Delivery Tracking
            'delivery_tracking' => $this->when(
                $this->relationLoaded('delivery'),
                fn() => new DeliveryResource($this->delivery)
            ),
            
            // Payment Information
            'payment' => $this->when(
                $this->relationLoaded('payment'),
                fn() => new PaymentResource($this->payment)
            ),
            
            // Status & Timeline
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            
            'actions_available' => [
                'can_cancel' => $this->canBeCancelled(),
                'can_rate' => $this->canBeRated(),
                'can_dispute' => $this->status->value === 'delivered' 
                    && $this->delivered_at 
                    && now()->diffInDays($this->delivered_at) <= config('agri-connect.orders.dispute_window_days', 3),
            ],
            
            'timeline' => [
                'created_at' => $this->created_at?->format('Y-m-d H:i'),
                'confirmed_at' => $this->confirmed_at?->format('Y-m-d H:i'),
                'paid_at' => $this->paid_at?->format('Y-m-d H:i'),
                'delivered_at' => $this->delivered_at?->format('Y-m-d H:i'),
                'completed_at' => $this->completed_at?->format('Y-m-d H:i'),
            ],
            
            // Delivery Performance
            'delivery_performance' => $this->when($this->delivered_at, [
                'on_time' => $this->isWithinGuaranteedTime(),
                'delivery_hours' => $this->confirmed_at && $this->delivered_at 
                    ? $this->confirmed_at->diffInHours($this->delivered_at) 
                    : null,
                'guaranteed_hours' => config('agri-connect.delivery.guarantee_hours', 48),
            ]),
            
            // Cancellation info
            'cancellation' => $this->when($this->status->value === 'cancelled', [
                'reason' => $this->cancellation_reason,
                'cancelled_at' => $this->cancelled_at ?? $this->updated_at->format('Y-m-d H:i'),
            ]),
            
            // Progress
            'progress' => $this->items_progress,
            
            // Metadata
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}