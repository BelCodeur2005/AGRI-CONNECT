<?php

// app/Traits/HasLocation.php
namespace App\Traits;

use App\Models\Location;

trait HasLocation
{
    /**
     * Relation vers location
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Obtenir nom complet de la localisation
     */
    public function getFullLocationAttribute(): string
    {
        return $this->location ? $this->location->full_name : 'Non défini';
    }

    /**
     * Vérifier si dans une zone
     */
    public function isInLocation(int $locationId): bool
    {
        return $this->location_id === $locationId;
    }

    /**
     * Scope par location
     */
    public function scopeInLocation($query, int $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    /**
     * Scope par région
     */
    public function scopeInRegion($query, string $region)
    {
        return $query->whereHas('location', function($q) use ($region) {
            $q->where('region', $region);
        });
    }
}
