<?php

// app/Services/Logistics/RouteOptimizationService.php
namespace App\Services\Logistics;

use App\Models\DeliveryGroup;
use App\Models\Delivery;
use Illuminate\Support\Collection;

class RouteOptimizationService
{
    /**
     * Créer groupe de livraison optimisé
     */
    public function createOptimizedGroup(Collection $deliveries, int $deliveryLocationId): DeliveryGroup
    {
        $group = DeliveryGroup::create([
            'delivery_location_id' => $deliveryLocationId,
            'delivery_address' => $deliveries->first()->delivery_address,
            'scheduled_date' => now()->addDay()->toDateString(),
            'status' => 'pending',
        ]);

        // Optimiser ordre des livraisons
        $optimizedSequence = $this->optimizeSequence($deliveries);

        foreach ($optimizedSequence as $index => $delivery) {
            $delivery->update([
                'delivery_group_id' => $group->id,
                'sequence_in_group' => $index + 1,
            ]);
        }

        $group->calculateTotals();

        return $group;
    }

    /**
     * Optimiser séquence de livraisons
     */
    private function optimizeSequence(Collection $deliveries): Collection
    {
        // Algorithme simple: trier par proximité géographique
        // TODO: Implémenter algorithme plus sophistiqué (TSP)
        
        return $deliveries->sortBy(function($delivery) {
            return $delivery->pickup_location_id;
        });
    }

    /**
     * Calculer coût optimisé
     */
    public function calculateOptimizedCost(Collection $deliveries): float
    {
        $baseCost = 5000;
        $countDiscount = min($deliveries->count() - 1, 5) * 1000; // 1000 FCFA de réduction par livraison supplémentaire (max 5)

        return max($baseCost - $countDiscount, 3000); // Minimum 3000 FCFA
    }
}