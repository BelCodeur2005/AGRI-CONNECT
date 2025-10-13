<?php

// app/Services/Analytics/BuyerAnalyticsService.php
namespace App\Services\Analytics;

use App\Models\Buyer;

class BuyerAnalyticsService
{
    public function getDashboard(Buyer $buyer): array
    {
        return [
            'overview' => [
                'total_orders' => $buyer->total_orders,
                'total_spent' => $buyer->total_spent,
                'average_order_value' => $buyer->average_order_value,
                'this_month_spending' => $buyer->monthly_spending,
            ],
            'spending_chart' => $this->getSpendingChart($buyer, 6),
            'favorite_products' => $this->getFavoriteProducts($buyer, 5),
            'favorite_producers' => $this->getFavoriteProducers($buyer, 5),
        ];
    }

    private function getSpendingChart(Buyer $buyer, int $months): array
    {
        $spending = $buyer->orders()
            ->where('created_at', '>=', now()->subMonths($months))
            ->where('status', 'completed')
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total_amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return [
            'labels' => $spending->map(fn($s) => "{$s->year}-{$s->month}")->toArray(),
            'data' => $spending->pluck('total')->toArray(),
        ];
    }

    private function getFavoriteProducts(Buyer $buyer, int $limit): array
    {
        return $buyer->orders()
            ->with('items.product')
            ->get()
            ->pluck('items')
            ->flatten()
            ->groupBy('product_id')
            ->map(function($items) {
                return [
                    'product' => $items->first()->product,
                    'order_count' => $items->count(),
                    'total_quantity' => $items->sum('quantity'),
                ];
            })
            ->sortByDesc('order_count')
            ->take($limit)
            ->values()
            ->toArray();
    }

    private function getFavoriteProducers(Buyer $buyer, int $limit): array
    {
        return $buyer->orders()
            ->with('items.producer.user')
            ->get()
            ->pluck('items')
            ->flatten()
            ->groupBy('producer_id')
            ->map(function($items) {
                return [
                    'producer' => $items->first()->producer,
                    'order_count' => $items->count(),
                    'total_spent' => $items->sum('subtotal'),
                ];
            })
            ->sortByDesc('total_spent')
            ->take($limit)
            ->values()
            ->toArray();
    }
}
