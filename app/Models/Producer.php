<?php

// app/Models/Producer.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producer extends Model
{
    use HasFactory;

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
    ];

    protected $casts = [
        'certifications' => 'array',
        'farm_size' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'total_revenue' => 'decimal:2',
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

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function activeOffers()
    {
        return $this->offers()->where('status', 'active');
    }

    // Helper methods
    public function updateRating()
    {
        $ratings = Rating::where('rateable_type', Producer::class)
            ->where('rateable_id', $this->id)
            ->get();

        $this->update([
            'average_rating' => $ratings->avg('overall_score') ?? 0,
            'total_ratings' => $ratings->count(),
        ]);
    }

    public function incrementRevenue(float $amount)
    {
        $this->increment('total_revenue', $amount);
        $this->increment('total_orders');
    }
}
