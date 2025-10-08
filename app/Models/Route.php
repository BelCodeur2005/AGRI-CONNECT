<?php

// app/Models/Route.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'origin_location_id',
        'destination_location_id',
        'distance_km',
        'estimated_duration_hours',
        'base_transport_cost',
        'road_conditions',
        'is_active',
        'all_weather',
        'waypoints',
    ];

    protected $casts = [
        'base_transport_cost' => 'decimal:2',
        'is_active' => 'boolean',
        'all_weather' => 'boolean',
        'waypoints' => 'array',
    ];

    // Relations
    public function originLocation()
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    public function destinationLocation()
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    // Helper methods
    public function calculateCost(float $weight): float
    {
        // Coût de base + coût par kg
        $costPerKg = 5; // 5 FCFA par kg (ajustable)
        return $this->base_transport_cost + ($weight * $costPerKg);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}