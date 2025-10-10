<?php

// app/Models/Transporter.php (MODIFIÉ - avec certification)
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasRatings;
use App\Traits\HasStatistics;
use App\Enums\TransporterCertificationLevel;

class Transporter extends Model
{
    use HasFactory, HasRatings, HasStatistics;

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
        'certification_level',
    ];

    protected $casts = [
        'max_capacity_kg' => 'decimal:2',
        'has_refrigeration' => 'boolean',
        'service_areas' => 'array',
        'average_rating' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'is_available' => 'boolean',
        'is_certified' => 'boolean',
        'certification_level' => TransporterCertificationLevel::class,
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

    public function deliveryGroups()
    {
        return $this->hasMany(DeliveryGroup::class);
    }

    // Helper methods
    public function activeDeliveries()
    {
        return $this->deliveries()
            ->whereIn('status', ['assigned', 'picked_up', 'in_transit']);
    }

    public function incrementEarnings(float $amount): void
    {
        $this->increment('total_earnings', $amount);
        $this->increment('total_deliveries');
        
        // Recalculer niveau certification
        $this->updateCertificationLevel();
    }

    public function canAcceptDelivery(float $weight): bool
    {
        return $this->is_available 
            && $this->is_certified 
            && $weight <= $this->max_capacity_kg
            && $this->activeDeliveries()->count() < 5; // Max 5 livraisons actives
    }

    public function serveLocation(int $locationId): bool
    {
        return in_array($locationId, $this->service_areas ?? []);
    }

    public function updateCertificationLevel(): void
    {
        $level = TransporterCertificationLevel::NONE;

        if ($this->average_rating >= 4.8 && $this->total_deliveries >= 500) {
            $level = TransporterCertificationLevel::PLATINUM;
        } elseif ($this->average_rating >= 4.5 && $this->total_deliveries >= 200) {
            $level = TransporterCertificationLevel::GOLD;
        } elseif ($this->average_rating >= 4.0 && $this->total_deliveries >= 50) {
            $level = TransporterCertificationLevel::SILVER;
        } elseif ($this->average_rating >= 3.5 && $this->total_deliveries >= 10) {
            $level = TransporterCertificationLevel::BRONZE;
        }

        $this->update(['certification_level' => $level]);
    }

    public function getOnTimeRateAttribute(): float
    {
        if ($this->total_deliveries === 0) {
            return 0;
        }

        $onTimeDeliveries = $this->deliveries()
            ->where('on_time', true)
            ->count();

        return round(($onTimeDeliveries / $this->total_deliveries) * 100, 1);
    }

    public function getBonusEarningsAttribute(): float
    {
        $bonusPercent = $this->certification_level->bonusPercentage();
        return round($this->total_earnings * ($bonusPercent / 100), 2);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
            ->where('is_certified', true);
    }

    public function scopeCertified($query)
    {
        return $query->where('is_certified', true);
    }

    public function scopeInArea($query, int $locationId)
    {
        return $query->whereJsonContains('service_areas', $locationId);
    }

    public function scopeWithCapacity($query, float $minWeight)
    {
        return $query->where('max_capacity_kg', '>=', $minWeight);
    }

    public function scopeTopRated($query, int $limit = 10)
    {
        return $query->where('average_rating', '>=', 4.5)
            ->where('total_deliveries', '>=', 20)
            ->orderBy('average_rating', 'desc')
            ->limit($limit);
    }
}