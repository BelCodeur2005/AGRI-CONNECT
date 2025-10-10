<?php

// app/Models/Producer.php (MODIFIÉ - avec traits)
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasRatings;
use App\Traits\HasLocation;
use App\Traits\HasStatistics;

class Producer extends Model
{
    use HasFactory, HasRatings, HasLocation, HasStatistics;

    protected $fillable = [
        'user_id',
        'location_id',
        'farm_name',
        'farm_size',
        'farm_address',
        'id_card_number',
        'id_card_photo',
        'certifications',
        'years_experience',
        'average_rating',
        'total_ratings',
        'total_orders',
        'total_revenue',
        'verification_status',
    ];

    protected $casts = [
        'certifications' => 'array',
        'farm_size' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'verification_status' => \App\Enums\ProducerVerificationStatus::class,
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, OrderItem::class, 'producer_id', 'id', 'id', 'order_id')
            ->distinct();
    }

    public function availability()
    {
        return $this->hasMany(ProducerAvailability::class);
    }

    public function paymentSplits()
    {
        return $this->hasMany(PaymentSplit::class);
    }

    // Helper methods
    public function activeOffers()
    {
        return $this->offers()->active();
    }

    public function incrementRevenue(float $amount): void
    {
        $this->increment('total_revenue', $amount);
        $this->increment('total_orders');
    }

    public function isAvailableOn(\DateTime $date): bool
    {
        $availability = $this->availability()
            ->forDate($date)
            ->first();

        return $availability ? $availability->is_available : true;
    }

    public function getPendingEarningsAttribute(): float
    {
        return $this->paymentSplits()
            ->held()
            ->sum('net_amount');
    }

    public function getTotalEarningsReleasedAttribute(): float
    {
        return $this->paymentSplits()
            ->released()
            ->sum('net_amount');
    }

    public function canCreateOffers(): bool
    {
        return $this->verification_status === \App\Enums\ProducerVerificationStatus::VERIFIED
            && $this->user->is_active;
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('verification_status', \App\Enums\ProducerVerificationStatus::VERIFIED);
    }

    public function scopeWithActiveOffers($query)
    {
        return $query->whereHas('offers', function($q) {
            $q->active();
        });
    }

    public function scopeTopRated($query, int $limit = 10)
    {
        return $query->where('average_rating', '>=', 4.0)
            ->where('total_ratings', '>=', 5)
            ->orderBy('average_rating', 'desc')
            ->limit($limit);
    }
}