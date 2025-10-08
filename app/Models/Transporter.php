<?php

// app/Models/Transporter.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transporter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_type',
        'vehicle_registration',
        'vehicle_photo',
        'driver_license_number',
        'driver_license_photo',
        'max_capacity_kg',
        'has_refrigeration',
        'service_areas',
        'average_rating',
        'total_ratings',
        'total_deliveries',
        'total_earnings',
        'is_available',
        'is_certified',
    ];

    protected $casts = [
        'max_capacity_kg' => 'decimal:2',
        'has_refrigeration' => 'boolean',
        'service_areas' => 'array',
        'average_rating' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'is_available' => 'boolean',
        'is_certified' => 'boolean',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function activeDeliveries()
    {
        return $this->deliveries()
            ->whereIn('status', ['assigned', 'picked_up', 'in_transit']);
    }

    // Helper methods
    public function updateRating()
    {
        $ratings = Rating::where('rateable_type', Transporter::class)
            ->where('rateable_id', $this->id)
            ->get();

        $this->update([
            'average_rating' => $ratings->avg('overall_score') ?? 0,
            'total_ratings' => $ratings->count(),
        ]);
    }

    public function incrementEarnings(float $amount)
    {
        $this->increment('total_earnings', $amount);
        $this->increment('total_deliveries');
    }

    public function canAcceptDelivery(float $weight): bool
    {
        return $this->is_available 
            && $this->is_certified 
            && $weight <= $this->max_capacity_kg;
    }
}
