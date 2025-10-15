<?php

namespace App\Http\Resources\Common;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\HasPriceCalculation;

class RouteResource extends JsonResource
{
    use HasPriceCalculation;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            
            // Route Information
            'name' => $this->name,
            
            // Locations
            'origin' => [
                'location_id' => $this->origin_location_id,
                'location' => new LocationResource($this->whenLoaded('originLocation')),
            ],
            
            'destination' => [
                'location_id' => $this->destination_location_id,
                'location' => new LocationResource($this->whenLoaded('destinationLocation')),
            ],
            
            // Distance & Duration
            'distance' => [
                'value' => (float) $this->distance_km,
                'unit' => 'km',
                'formatted' => number_format($this->distance_km, 1) . ' km',
            ],
            
            'estimated_duration' => [
                'value' => (float) $this->estimated_duration_hours,
                'unit' => 'hours',
                'formatted' => $this->formatDuration($this->estimated_duration_hours),
            ],
            
            // Pricing
            'base_transport_cost' => [
                'amount' => (float) $this->base_transport_cost,
                'formatted' => $this->formatPrice($this->base_transport_cost),
            ],
            
            // Calculate cost for specific weight
            'cost_calculator' => [
                'base_cost' => (float) $this->base_transport_cost,
                'per_kg_rate' => config('agri-connect.delivery.fee_per_kg', 5),
                'example_10kg' => [
                    'amount' => $this->calculateCost(10),
                    'formatted' => $this->formatPrice($this->calculateCost(10)),
                ],
                'example_50kg' => [
                    'amount' => $this->calculateCost(50),
                    'formatted' => $this->formatPrice($this->calculateCost(50)),
                ],
                'example_100kg' => [
                    'amount' => $this->calculateCost(100),
                    'formatted' => $this->formatPrice($this->calculateCost(100)),
                ],
            ],
            
            // Route Details
            'road_conditions' => $this->road_conditions,
            'is_active' => $this->is_active,
            'all_weather' => $this->all_weather,
            'accessibility' => [
                'all_weather' => $this->all_weather,
                'road_quality' => $this->road_conditions,
                'recommended_vehicle' => $this->getRecommendedVehicle(),
            ],
            
            // Waypoints (intermediate stops)
            'waypoints' => $this->when($this->waypoints, function() {
                return collect($this->waypoints)->map(function($waypoint) {
                    return [
                        'name' => $waypoint['name'] ?? null,
                        'order' => $waypoint['order'] ?? null,
                        'distance_from_origin' => $waypoint['distance_km'] ?? null,
                    ];
                });
            }),
            
            // Status
            'status' => [
                'is_active' => $this->is_active,
                'label' => $this->is_active ? 'Active' : 'Inactive',
                'availability' => $this->all_weather ? 'Toute saison' : 'Saisonnier',
            ],
            
            // Statistics (when loaded)
            'statistics' => $this->when(isset($this->deliveries_count), [
                'total_deliveries' => $this->deliveries_count ?? 0,
                'this_month' => $this->deliveries_this_month ?? 0,
                'average_duration_actual' => $this->when(
                    isset($this->avg_delivery_hours),
                    fn() => $this->formatDuration($this->avg_delivery_hours)
                ),
            ]),
            
            // Popularity & Usage
            'popularity' => $this->when(isset($this->popularity_score), [
                'score' => $this->popularity_score,
                'rank' => $this->popularity_rank ?? null,
                'frequently_used' => ($this->deliveries_count ?? 0) > 10,
            ]),
            
            // Delivery Performance
            'performance' => $this->when(isset($this->on_time_rate), [
                'on_time_rate' => round($this->on_time_rate, 1) . '%',
                'average_delay_minutes' => $this->avg_delay_minutes ?? 0,
                'reliability' => $this->getReliabilityLevel(),
            ]),
            
            // Metadata
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
    
    /**
     * Format duration in human-readable format
     */
    protected function formatDuration(float $hours): string
    {
        if ($hours < 1) {
            return round($hours * 60) . ' min';
        }
        
        $h = floor($hours);
        $m = round(($hours - $h) * 60);
        
        if ($m === 0) {
            return $h . 'h';
        }
        
        return $h . 'h' . $m . 'min';
    }
    
    /**
     * Get recommended vehicle type based on road conditions
     */
    protected function getRecommendedVehicle(): string
    {
        return match($this->road_conditions) {
            'excellent', 'good' => 'Tous véhicules',
            'fair' => 'Pick-up ou Van',
            'poor' => '4x4 recommandé',
            'very_poor' => '4x4 obligatoire',
            default => 'Non spécifié',
        };
    }
    
    /**
     * Get reliability level based on on-time rate
     */
    protected function getReliabilityLevel(): string
    {
        if (!isset($this->on_time_rate)) {
            return 'Non évalué';
        }
        
        return match(true) {
            $this->on_time_rate >= 90 => 'Excellente',
            $this->on_time_rate >= 75 => 'Bonne',
            $this->on_time_rate >= 60 => 'Moyenne',
            default => 'À améliorer',
        };
    }
}   