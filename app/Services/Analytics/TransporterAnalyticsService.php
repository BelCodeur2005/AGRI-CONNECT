<?php

// app/Services/Analytics/TransporterAnalyticsService.php
namespace App\Services\Analytics;

use App\Models\Transporter;

class TransporterAnalyticsService
{
    public function getDashboard(Transporter $transporter): array
    {
        return [
            'overview' => [
                'total_deliveries' => $transporter->total_deliveries,
                'total_earnings' => $transporter->total_earnings,
                'average_rating' => $transporter->average_rating,
                'on_time_rate' => $transporter->on_time_rate,
                'certification_level' => $transporter->certification_level->label(),
                'bonus_earnings' => $transporter->bonus_earnings,
            ],
            'earnings_chart' => $this->getEarningsChart($transporter, 6),
            'performance_metrics' => $this->getPerformanceMetrics($transporter),
        ];
    }

    private function getEarningsChart(Transporter $transporter, int $months): array
    {
        $earnings = $transporter->deliveries()
            ->where('created_at', '>=', now()->subMonths($months))
            ->where('status', 'delivered')
            ->join('orders', 'deliveries.order_id', '=', 'orders.id')
            ->selectRaw('YEAR(deliveries.created_at) as year, MONTH(deliveries.created_at) as month, SUM(orders.delivery_cost) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return [
            'labels' => $earnings->map(fn($e) => "{$e->year}-{$e->month}")->toArray(),
            'data' => $earnings->pluck('total')->toArray(),
        ];
    }

    private function getPerformanceMetrics(Transporter $transporter): array
    {
        $deliveries = $transporter->deliveries()->where('status', 'delivered')->get();

        return [
            'total_deliveries' => $deliveries->count(),
            'on_time_deliveries' => $deliveries->where('on_time', true)->count(),
            'average_delay_minutes' => round($deliveries->where('on_time', false)->avg('delay_minutes') ?? 0, 1),
            'completion_rate' => $transporter->total_deliveries > 0
                ? round(($deliveries->count() / $transporter->total_deliveries) * 100, 1)
                : 0,
        ];
    }
}
