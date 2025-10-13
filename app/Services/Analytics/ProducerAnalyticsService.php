<?php

// app/Services/Analytics/ProducerAnalyticsService.php
namespace App\Services\Analytics;

use App\Models\Producer;
use Illuminate\Support\Facades\DB;

class ProducerAnalyticsService
{
    /**
     * Dashboard statistiques
     */
    public function getDashboard(Producer $producer): array
    {
        return [
            'overview' => $this->getOverview($producer),
            'sales_chart' => $this->getSalesChart($producer, 30),
            'top_products' => $this->getTopProducts($producer, 5),
            'pending_earnings' => $producer->pending_earnings,
            'recent_orders' => $this->getRecentOrders($producer, 5),
        ];
    }

    /**
     * Vue d'ensemble
     */
    private function getOverview(Producer $producer): array
    {
        $thisMonth = $producer->orderItems()
            ->where('created_at', '>=', now()->startOfMonth())
            ->where('status', 'completed')
            ->sum('subtotal');

        $lastMonth = $producer->orderItems()
            ->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth()
            ])
            ->where('status', 'completed')
            ->sum('subtotal');

        $growth = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 0;

        return [
            'total_revenue' => $producer->total_revenue,
            'total_orders' => $producer->total_orders,
            'average_rating' => $producer->average_rating,
            'active_offers' => $producer->offers()->active()->count(),
            'this_month_revenue' => $thisMonth,
            'growth_percentage' => round($growth, 1),
        ];
    }

    /**
     * Graphique des ventes
     */
    private function getSalesChart(Producer $producer, int $days): array
    {
        $sales = $producer->orderItems()
            ->where('created_at', '>=', now()->subDays($days))
            ->where('status', 'completed')
            ->selectRaw('DATE(created_at) as date, SUM(subtotal) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $sales->pluck('date')->toArray(),
            'data' => $sales->pluck('total')->toArray(),
        ];
    }

    /**
     * Top produits
     */
    private function getTopProducts(Producer $producer, int $limit): array
    {
        return $producer->orderItems()
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_revenue'))
            ->where('status', 'completed')
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Commandes récentes
     */
    private function getRecentOrders(Producer $producer, int $limit)
    {
        return $producer->orderItems()
            ->with(['order.buyer.user', 'product'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}