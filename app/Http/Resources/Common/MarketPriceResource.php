<?php

namespace App\Http\Resources\Common;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Catalog\ProductResource;
use App\Traits\HasPriceCalculation;

class MarketPriceResource extends JsonResource
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
            
            // Product Information
            'product' => new ProductResource($this->whenLoaded('product')),
            
            // Location
            'location' => new LocationResource($this->whenLoaded('location')),
            
            // Price Information
            'prices' => [
                'min' => [
                    'amount' => (float) $this->min_price,
                    'formatted' => $this->formatPrice($this->min_price),
                ],
                'max' => [
                    'amount' => (float) $this->max_price,
                    'formatted' => $this->formatPrice($this->max_price),
                ],
                'average' => [
                    'amount' => (float) $this->avg_price,
                    'formatted' => $this->formatPrice($this->avg_price),
                ],
                'suggested' => [
                    'amount' => (float) $this->suggested_price,
                    'formatted' => $this->formatPrice($this->suggested_price),
                    'reason' => 'Prix optimal basé sur le marché actuel',
                ],
            ],
            
            // Price Range Analysis
            'price_range' => [
                'spread' => [
                    'amount' => $this->max_price - $this->min_price,
                    'formatted' => $this->formatPrice($this->max_price - $this->min_price),
                ],
                'spread_percentage' => $this->min_price > 0 
                    ? round((($this->max_price - $this->min_price) / $this->min_price) * 100, 1)
                    : 0,
                'volatility' => $this->getPriceVolatility(),
            ],
            
            // Price Positioning
            'positioning' => [
                'suggested_vs_min' => [
                    'difference' => $this->suggested_price - $this->min_price,
                    'percentage' => $this->min_price > 0
                        ? round((($this->suggested_price - $this->min_price) / $this->min_price) * 100, 1)
                        : 0,
                ],
                'suggested_vs_avg' => [
                    'difference' => $this->suggested_price - $this->avg_price,
                    'percentage' => $this->avg_price > 0
                        ? round((($this->suggested_price - $this->avg_price) / $this->avg_price) * 100, 1)
                        : 0,
                ],
                'suggested_vs_max' => [
                    'difference' => $this->suggested_price - $this->max_price,
                    'percentage' => $this->max_price > 0
                        ? round((($this->suggested_price - $this->max_price) / $this->max_price) * 100, 1)
                        : 0,
                ],
            ],
            
            // Competitive Analysis
            'competitive_position' => [
                'is_competitive' => $this->suggested_price <= $this->avg_price * 1.1,
                'is_below_market' => $this->suggested_price < $this->avg_price,
                'is_premium' => $this->suggested_price > $this->avg_price * 1.2,
                'recommendation' => $this->getPricingRecommendation(),
            ],
            
            // Pricing Suggestions for Producers
            'producer_guidance' => [
                'quick_sell_price' => [
                    'amount' => round($this->min_price * 1.05, 0),
                    'formatted' => $this->formatPrice(round($this->min_price * 1.05, 0)),
                    'label' => 'Prix pour vente rapide',
                ],
                'standard_price' => [
                    'amount' => (float) $this->suggested_price,
                    'formatted' => $this->formatPrice($this->suggested_price),
                    'label' => 'Prix recommandé',
                ],
                'premium_price' => [
                    'amount' => round($this->max_price * 0.95, 0),
                    'formatted' => $this->formatPrice(round($this->max_price * 0.95, 0)),
                    'label' => 'Prix premium (qualité supérieure)',
                ],
            ],
            
            // Date & Validity
            'date_info' => [
                'recorded_date' => $this->price_date?->format('Y-m-d'),
                'recorded_date_formatted' => $this->price_date?->translatedFormat('d F Y'),
                'is_today' => $this->price_date?->isToday() ?? false,
                'is_recent' => $this->price_date 
                    ? now()->diffInDays($this->price_date) <= 7
                    : false,
                'age_in_days' => $this->price_date 
                    ? now()->diffInDays($this->price_date)
                    : null,
                'freshness' => $this->getDataFreshness(),
            ],
            
            // Data Source & Reliability
            'metadata' => [
                'source' => $this->source ?? 'Agri-Connect Platform',
                'data_points' => $this->data_points_count ?? 1,
                'confidence_level' => $this->getConfidenceLevel(),
                'last_updated' => $this->updated_at?->diffForHumans(),
            ],
            
            // Market Context Indicators
            'market_indicators' => [
                'high_demand' => $this->avg_price > ($this->min_price * 1.3),
                'stable_market' => ($this->max_price - $this->min_price) / $this->avg_price < 0.2,
                'favorable_for_sellers' => $this->avg_price > $this->suggested_price * 0.95,
                'favorable_for_buyers' => $this->avg_price < $this->suggested_price * 1.05,
            ],
            
            // Alerts & Warnings
            'alerts' => $this->generateAlerts(),
            
            // Historical Trend (if available)
            'trend' => $this->when(isset($this->price_trend), [
                'direction' => $this->price_trend, // 'up', 'down', 'stable'
                'change_7days' => $this->price_change_7days ?? null,
                'change_30days' => $this->price_change_30days ?? null,
            ]),
            
            // Metadata
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
    
    /**
     * Get price volatility level
     */
    protected function getPriceVolatility(): string
    {
        if ($this->min_price == 0) {
            return 'unknown';
        }
        
        $spreadPercent = (($this->max_price - $this->min_price) / $this->min_price) * 100;
        
        return match(true) {
            $spreadPercent < 10 => 'Très faible',
            $spreadPercent < 20 => 'Faible',
            $spreadPercent < 35 => 'Modérée',
            $spreadPercent < 50 => 'Élevée',
            default => 'Très élevée',
        };
    }
    
    /**
     * Get pricing recommendation
     */
    protected function getPricingRecommendation(): string
    {
        $position = ($this->suggested_price - $this->min_price) / ($this->max_price - $this->min_price);
        
        return match(true) {
            $position < 0.3 => 'Prix bas - Vente rapide assurée',
            $position < 0.5 => 'Prix compétitif - Bon équilibre',
            $position < 0.7 => 'Prix moyen - Standard marché',
            $position < 0.9 => 'Prix élevé - Qualité requise',
            default => 'Prix premium - Excellence exigée',
        };
    }
    
    /**
     * Get data freshness indicator
     */
    protected function getDataFreshness(): string
    {
        if (!$this->price_date) {
            return 'unknown';
        }
        
        $daysOld = now()->diffInDays($this->price_date);
        
        return match(true) {
            $daysOld === 0 => 'Aujourd\'hui',
            $daysOld === 1 => 'Hier',
            $daysOld <= 3 => 'Récent',
            $daysOld <= 7 => 'Cette semaine',
            $daysOld <= 30 => 'Ce mois',
            default => 'Ancien',
        };
    }
    
    /**
     * Get confidence level
     */
    protected function getConfidenceLevel(): string
    {
        $dataPoints = $this->data_points_count ?? 1;
        $daysOld = $this->price_date ? now()->diffInDays($this->price_date) : 999;
        
        if ($dataPoints >= 10 && $daysOld <= 3) {
            return 'Très élevé';
        } elseif ($dataPoints >= 5 && $daysOld <= 7) {
            return 'Élevé';
        } elseif ($dataPoints >= 3 && $daysOld <= 14) {
            return 'Moyen';
        } else {
            return 'Faible';
        }
    }
    
    /**
     * Generate price alerts
     */
    protected function generateAlerts(): array
    {
        $alerts = [];
        
        // Alert si prix suggéré très différent de la moyenne
        if ($this->avg_price > 0) {
            $diff = abs($this->suggested_price - $this->avg_price) / $this->avg_price;
            if ($diff > 0.2) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => 'Prix suggéré s\'écarte significativement de la moyenne du marché',
                ];  
            }
        }
        
        // Alert si données anciennes
        if ($this->price_date && now()->diffInDays($this->price_date) > 7) {
            $alerts[] = [
                'type' => 'info',
                'message' => 'Les données de prix datent de plus de 7 jours',
            ];
        }
        
        // Alert si forte volatilité
        $spreadPercent = $this->min_price > 0 
            ? (($this->max_price - $this->min_price) / $this->min_price) * 100
            : 0;
            
        if ($spreadPercent > 50) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Forte volatilité des prix - Marché instable',
            ];
        }
        
        return $alerts;
    }
}