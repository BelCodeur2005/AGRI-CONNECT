<?php

// app/Traits/HasTimeframes.php
namespace App\Traits;

use Carbon\Carbon;

trait HasTimeframes
{
    /**
     * Scope aujourd'hui
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope cette semaine
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    /**
     * Scope ce mois
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    /**
     * Scope cette année
     */
    public function scopeThisYear($query)
    {
        return $query->whereYear('created_at', now()->year);
    }

    /**
     * Scope derniers X jours
     */
    public function scopeLastDays($query, int $days)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope entre dates
     */
    public function scopeBetweenDates($query, Carbon $from, Carbon $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Obtenir durée depuis création
     */
    public function getAgeInDaysAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Obtenir durée formatée
     */
    public function getAgeFormattedAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
}