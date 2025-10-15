<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Payments\PaymentSplitResource;
use App\Http\Resources\Common\LocationResource;
use App\Traits\HasPriceCalculation;

class ProducerOrderResource extends JsonResource
{
    use HasPriceCalculation;

    /**
     * Transform the resource into an array - Vue Producteur
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Filter items for this producer only
        $producerId = $request->user()?->producer?->id;
        $myItems = $this->items->filter(fn($item) => $item->producer_id === $producerId);
        
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            
            // Buyer Information
            'buyer' => [
                'id' => $this->buyer->id,
                'name' => $this->buyer->user->name,
                'business_name' => $this->buyer->business_name,
                'business_type' => $this->buyer->business_type,
                'phone' => $this->buyer->user->phone,
                'location' => $this->buyer->location?->full_name,
                'average_rating' => (float) $this->buyer->average_rating,
                'total_orders' => $this->buyer->total_orders,
            ],
            
            // My Items (for this producer)
            'my_items' => $myItems->map(fn($item) => [
                'id' => $item->id,
                'offer_id' => $item->offer_id,
                'product' => [
                    'id' => $item->product_id,
                    'name' => $item->product_name,
                    'image_url' => $item->product->image_url ?? null,
                ],
                'quantity' => (float) $item->quantity,
                'unit' => $item->product->unit->shortLabel(),
                'unit_price' => [
                    'amount' => $item->unit_price,
                    'formatted' => $this->formatPrice($item->unit_price),
                ],
                'subtotal' => [
                    'amount' => $item->subtotal,
                    'formatted' => $this->formatPrice($item->subtotal),
                ],
                'status' => [
                    'value' => $item->status->value,
                    'label' => $item->status->label(),
                    'color' => $item->status->color(),
                ],
                'actions' => [
                    'can_confirm' => $item->status->canBeConfirmed(),
                    'can_cancel' => $item->status->canBeCancelled(),
                ],
                'confirmed_at' => $item->confirmed_at?->format('Y-m-d H:i'),
                'ready_at' => $item->ready_at?->format('Y-m-d H:i'),
                'producer_notes' => $item->producer_notes,
            ])->values(),
            
            // Financial Summary (for this producer)
            'financial_summary' => [
                'items_total' => [
                    'amount' => $myItems->sum('subtotal'),
                    'formatted' => $this->formatPrice($myItems->sum('subtotal')),
                ],
                'platform_commission' => [
                    'amount' => $myItems->sum('platform_commission'),
                    'formatted' => $this->formatPrice($myItems->sum('platform_commission')),
                    'rate' => config('agri-connect.platform_commission', 7) . '%',
                ],
                'net_amount' => [
                    'amount' => $myItems->sum('producer_earnings'),
                    'formatted' => $this->formatPrice($myItems->sum('producer_earnings')),
                ],
                'breakdown' => [
                    'subtotal' => $myItems->sum('subtotal'),
                    'commission' => $myItems->sum('platform_commission'),
                    'you_receive' => $myItems->sum('producer_earnings'),
                ],
            ],
            
            // Payment Split (for this producer)
            'my_payment_split' => $this->when(
                $this->relationLoaded('payment') && $this->payment,
                function() use ($producerId) {
                    $split = $this->payment->splits->firstWhere('producer_id', $producerId);
                    return $split ? new PaymentSplitResource($split) : null;
                }
            ),
            
            // Delivery Information
            'delivery' => [
                'address' => $this->delivery_address,
                'location' => new LocationResource($this->whenLoaded('deliveryLocation')),
                'notes' => $this->delivery_notes,
                'requested_date' => $this->requested_delivery_date?->format('Y-m-d H:i'),
            ],
            
            // Pickup Information (from my offers)
            'pickup_info' => $this->when($myItems->isNotEmpty(), function() use ($myItems) {
                $firstOffer = $myItems->first()->offer;
                return [
                    'location' => $firstOffer->location 
                        ? new LocationResource($firstOffer->location)
                        : null,
                    'address' => $firstOffer->pickup_address ?? $firstOffer->producer->farm_address,
                ];
            }),
            
            // Status & Actions
            'order_status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            
            'items_status' => [
                'all_confirmed' => $myItems->every(fn($item) => $item->status->value === 'confirmed'),
                'all_ready' => $myItems->every(fn($item) => $item->status->value === 'ready'),
                'pending_count' => $myItems->where('status.value', 'pending')->count(),
                'confirmed_count' => $myItems->where('status.value', 'confirmed')->count(),
                'ready_count' => $myItems->where('status.value', 'ready')->count(),
                'cancelled_count' => $myItems->whereIn('status.value', ['cancelled', 'out_of_stock'])->count(),
            ],
            
            // Actions available
            'available_actions' => [
                'can_confirm_items' => $myItems->where('status.value', 'pending')->isNotEmpty(),
                'can_mark_ready' => $myItems->every(fn($item) => $item->status->value === 'confirmed') 
                    && $myItems->isNotEmpty(),
                'can_contact_buyer' => true,
                'needs_action' => $myItems->where('status.value', 'pending')->isNotEmpty(),
            ],
            
            // Timeline
            'timeline' => [
                'ordered_at' => $this->created_at?->format('Y-m-d H:i'),
                'items_confirmed_at' => $myItems->whereNotNull('confirmed_at')->max('confirmed_at')?->format('Y-m-d H:i'),
                'all_ready_at' => $myItems->every(fn($item) => $item->status->value === 'ready')
                    ? $myItems->max('ready_at')?->format('Y-m-d H:i')
                    : null,
                'collected_at' => $myItems->whereNotNull('collected_at')->max('collected_at')?->format('Y-m-d H:i'),
                'delivered_at' => $this->delivered_at?->format('Y-m-d H:i'),
            ],
            
            // Buyer Statistics
            'buyer_stats' => $this->when(isset($this->buyer->total_orders), [
                'total_orders' => $this->buyer->total_orders,
                'average_order_value' => $this->buyer->average_order_value,
                'reliability_score' => $this->buyer->average_rating >= 4.0 ? 'Excellent' : 'Bon',
            ]),
            
            // Contact & Communication
            'communication' => [
                'buyer_phone' => $this->buyer->user->phone,
                'delivery_phone' => $this->buyer->user->phone,
                'preferred_contact' => 'phone', // WhatsApp, SMS, Call
            ],
            
            // Notes
            'order_notes' => $this->delivery_notes,
            'my_items_notes' => $myItems->pluck('producer_notes')->filter()->implode('; '),
            
            // Metadata
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}