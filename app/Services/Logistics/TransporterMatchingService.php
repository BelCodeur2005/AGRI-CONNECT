<?php

// app/Services/Logistics/TransporterMatchingService.php
namespace App\Services\Logistics;

use App\Models\Transporter;

class TransporterMatchingService
{
    /**
     * Trouver meilleur transporteur
     */
    public function findBest(int $pickupLocationId, int $deliveryLocationId, float $weight): ?Transporter
    {
        return Transporter::available()
            ->withCapacity($weight)
            ->where(function($query) use ($pickupLocationId, $deliveryLocationId) {
                $query->whereJsonContains('service_areas', $pickupLocationId)
                      ->orWhereJsonContains('service_areas', $deliveryLocationId);
            })
            ->orderBy('average_rating', 'desc')
            ->orderBy('total_deliveries', 'desc')
            ->first();
    }

    /**
     * Obtenir transporteurs disponibles pour une zone
     */
    public function getAvailableInArea(int $locationId, float $minCapacity = 0)
    {
        return Transporter::available()
            ->inArea($locationId)
            ->withCapacity($minCapacity)
            ->orderBy('average_rating', 'desc')
            ->get();
    }
}