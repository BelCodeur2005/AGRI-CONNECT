<?php

// app/Traits/HasStatistics.php
namespace App\Traits;

trait HasStatistics
{
    /**
     * Incrémenter un compteur
     */
    public function incrementCounter(string $field, float $value = 1): void
    {
        $this->increment($field, $value);
    }

    /**
     * Décrémenter un compteur
     */
    public function decrementCounter(string $field, float $value = 1): void
    {
        $this->decrement($field, $value);
    }

    /**
     * Réinitialiser statistiques
     */
    public function resetStatistics(array $fields): void
    {
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = 0;
        }
        $this->update($data);
    }

    /**
     * Obtenir croissance période
     */
    public function getGrowth(string $field, string $period = 'month'): array
    {
        $currentPeriodStart = match($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $previousPeriodStart = match($period) {
            'day' => now()->subDay()->startOfDay(),
            'week' => now()->subWeek()->startOfWeek(),
            'month' => now()->subMonth()->startOfMonth(),
            'year' => now()->subYear()->startOfYear(),
            default => now()->subMonth()->startOfMonth(),
        };

        // Cette méthode devra être surchargée dans chaque model
        // selon le contexte (orders, sales, etc.)
        
        return [
            'current' => 0,
            'previous' => 0,
            'growth_percentage' => 0,
        ];
    }
}