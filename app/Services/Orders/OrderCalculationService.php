<?php

// app/Services/Orders/OrderCalculationService.php
namespace App\Services\Orders;

use App\Models\Order;
use App\Traits\HasPriceCalculation;

class OrderCalculationService
{
    use HasPriceCalculation;

    /**
     * Calculer frais de livraison
     */
    public function calculateDeliveryCost(Order $order, array $pickupLocationIds, int $deliveryLocationId): float
    {
        $uniqueProducers = count($pickupLocationIds);

        // Coût de base
        $baseCost = 5000;

        // Coût supplémentaire par producteur
        $additionalCost = ($uniqueProducers - 1) * 2000;

        // Ajustement selon distance (à implémenter avec vraies distances)
        $distanceMultiplier = 1.0;

        return ($baseCost + $additionalCost) * $distanceMultiplier;
    }

    /**
     * Calculer commission
     */
    public function calculateCommission(float $subtotal): float
    {
        return $this->calculatePlatformCommission($subtotal);
    }

    /**
     * Recalculer totaux de la commande
     */
    public function recalculateTotals(Order $order): void
    {
        $order->calculateTotals();
    }
}
