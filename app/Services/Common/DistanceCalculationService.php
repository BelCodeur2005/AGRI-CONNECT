<?php

// app/Services/Common/DistanceCalculationService.php
namespace App\Services\Common;

use App\Models\Location;

class DistanceCalculationService
{
    /**
     * Calculer distance entre deux locations (formule Haversine)
     */
    public function calculate(int $fromLocationId, int $toLocationId): float
    {
        $from = Location::findOrFail($fromLocationId);
        $to = Location::findOrFail($toLocationId);

        if (!$from->latitude || !$to->latitude) {
            // Si pas de coordonnées, retourner estimation
            return 100; // 100km par défaut
        }

        return $this->haversineDistance(
            $from->latitude,
            $from->longitude,
            $to->latitude,
            $to->longitude
        );
    }

    /**
     * Formule Haversine
     */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $latDiff = deg2rad($lat2 - $lat1);
        $lonDiff = deg2rad($lon2 - $lon1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Calculer temps de trajet estimé
     */
    public function estimateTravelTime(float $distanceKm, float $averageSpeedKmh = 50): float
    {
        // Temps en heures
        return round($distanceKm / $averageSpeedKmh, 2);
    }

    /**
     * Vérifier si distance acceptable
     */
    public function isWithinRange(int $fromLocationId, int $toLocationId, float $maxDistanceKm): bool
    {
        $distance = $this->calculate($fromLocationId, $toLocationId);
        return $distance <= $maxDistanceKm;
    }
}
