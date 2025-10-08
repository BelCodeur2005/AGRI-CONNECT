<?php

// app/Models/Buyer.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    use HasFactory;

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

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    // Helper methods
    public function updateRating()
    {
        $ratings = Rating::where('rateable_type', Buyer::class)
            ->where('rateable_id', $this->id)
            ->get();

        $this->update([
            'average_rating' => $ratings->avg('overall_score') ?? 0,
            'total_ratings' => $ratings->count(),
        ]);
    }

    public function incrementSpent(float $amount)
    {
        $this->increment('total_spent', $amount);
        $this->increment('total_orders');
    }
}