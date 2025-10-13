<?php

// app/Services/Common/PricingSuggestionService.php (DÉJÀ EXISTANT - COMPLÉTER)
namespace App\Services\Common;

use App\Models\Product;
use App\Models\MarketPrice;
use App\Models\Offer;
use Illuminate\Support\Facades\Cache;

class PricingSuggestionService
{
    /**
     * Suggérer prix pour un produit
     */
    public function suggestPrice(int $productId, int $locationId): ?array
    {
        $cacheKey = "price_suggestion_{$productId}_{$locationId}";

        return Cache::remember($cacheKey, 3600, function() use ($productId, $locationId) {
            // 1. Chercher prix du marché récent
            $marketPrice = MarketPrice::where('product_id', $productId)
                ->where('location_id', $locationId)
                ->whereDate('price_date', today())
                ->first();

            if ($marketPrice) {
                return [
                    'suggested_price' => $marketPrice->suggested_price,
                    'min_price' => $marketPrice->min_price,
                    'max_price' => $marketPrice->max_price,
                    'avg_price' => $marketPrice->avg_price,
                    'source' => 'market_data',
                ];
            }

            // 2. Calculer à partir des offres récentes
            $recentOffers = Offer::where('product_id', $productId)
                ->where('location_id', $locationId)
                ->where('created_at', '>=', now()->subDays(7))
                ->get();

            if ($recentOffers->isNotEmpty()) {
                $avgPrice = $recentOffers->avg('price_per_unit');
                $minPrice = $recentOffers->min('price_per_unit');
                $maxPrice = $recentOffers->max('price_per_unit');

                return [
                    'suggested_price' => round($avgPrice),
                    'min_price' => round($minPrice),
                    'max_price' => round($maxPrice),
                    'avg_price' => round($avgPrice),
                    'source' => 'recent_offers',
                ];
            }

            return null;
        });
    }

    /**
     * Analyser tendance des prix
     */
    public function analyzePriceTrend(int $productId, int $locationId, int $days = 30): array
    {
        $prices = MarketPrice::where('product_id', $productId)
            ->where('location_id', $locationId)
            ->where('price_date', '>=', now()->subDays($days))
            ->orderBy('price_date', 'asc')
            ->get();

        if ($prices->isEmpty()) {
            return ['trend' => 'stable', 'change_percent' => 0];
        }

        $firstPrice = $prices->first()->avg_price;
        $lastPrice = $prices->last()->avg_price;
        $changePercent = (($lastPrice - $firstPrice) / $firstPrice) * 100;

        $trend = match(true) {
            $changePercent > 10 => 'increasing',
            $changePercent < -10 => 'decreasing',
            default => 'stable',
        };

        return [
            'trend' => $trend,
            'change_percent' => round($changePercent, 2),
            'first_price' => $firstPrice,
            'last_price' => $lastPrice,
        ];
    }

    /**
     * Recommander prix optimal
     */
    public function recommendOptimalPrice(int $productId, int $locationId, float $quantity): array
    {
        $suggestion = $this->suggestPrice($productId, $locationId);

        if (!$suggestion) {
            return ['recommended' => null, 'reason' => 'no_data'];
        }

        // Ajuster selon quantité (plus de quantité = prix légèrement plus bas)
        $volumeDiscount = $quantity > 100 ? 0.95 : 1.0;
        
        $recommended = round($suggestion['suggested_price'] * $volumeDiscount);

        return [
            'recommended' => $recommended,
            'market_avg' => $suggestion['avg_price'],
            'potential_revenue' => $recommended * $quantity,
            'reason' => 'market_based',
        ];
    }
}