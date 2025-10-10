<?php

// app/Models/Buyer.php (MODIFIÉ - avec traits)
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasRatings;
use App\Traits\HasLocation;
use App\Traits\HasStatistics;

class Buyer extends Model
{
    use HasFactory, HasRatings, HasLocation, HasStatistics;

    protected $fillable = [
        'user_id',
        'location_id',
        'business_name',
        'business_type',
        'business_license',
        'delivery_address',
        'tax_id',
        'stars_rating',
        'average_rating',
        'total_ratings',
        'total_orders',
        'total_spent',
    ];

    protected $casts = [
        'average_rating' => 'decimal:2',
        'total_spent' => 'decimal:2',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'user_id', 'user_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id', 'user_id');
    }

    // Helper methods
    public function incrementSpent(float $amount): void
    {
        $this->increment('total_spent', $amount);
        $this->increment('total_orders');
    }

    public function getCartSummaryAttribute(): array
    {
        $items = $this->cartItems()->valid()->with('offer')->get();
        
        return [
            'total_items' => $items->count(),
            'total_amount' => $items->sum('subtotal'),
            'unique_producers' => $items->pluck('offer.producer_id')->unique()->count(),
        ];
    }

    public function getAverageOrderValueAttribute(): float
    {
        if ($this->total_orders === 0) {
            return 0;
        }

        return round($this->total_spent / $this->total_orders, 2);
    }

    public function getMonthlySpendingAttribute(): float
    {
        return $this->orders()
            ->thisMonth()
            ->where('status', 'completed')
            ->sum('total_amount');
    }

    // Scopes
    public function scopePremium($query)
    {
        return $query->where('total_spent', '>=', 1000000) // 1M FCFA
            ->where('total_orders', '>=', 10);
    }

    public function scopeActive($query)
    {
        return $query->whereHas('orders', function($q) {
            $q->where('created_at', '>=', now()->subMonths(3));
        });
    }
}
