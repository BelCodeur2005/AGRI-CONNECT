<?php

// app/Models/Rating.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'rater_id',
        'rateable_type',
        'rateable_id',
        'overall_score',
        'quality_score',
        'punctuality_score',
        'communication_score',
        'packaging_score',
        'comment',
        'admin_response',
        'is_verified',
        'is_featured',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
    ];

    // Relations
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function rateable()
    {
        return $this->morphTo();
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::created(function ($rating) {
            // Mettre à jour la note moyenne du profil noté
            $rating->rateable->updateRating();
        });

        static::updated(function ($rating) {
            $rating->rateable->updateRating();
        });

        static::deleted(function ($rating) {
            $rating->rateable->updateRating();
        });
    }

    // Scopes
    public function scopeForProducer($query, $producerId)
    {
        return $query->where('rateable_type', Producer::class)
            ->where('rateable_id', $producerId);
    }

    public function scopeForBuyer($query, $buyerId)
    {
        return $query->where('rateable_type', Buyer::class)
            ->where('rateable_id', $buyerId);
    }

    public function scopeForTransporter($query, $transporterId)
    {
        return $query->where('rateable_type', Transporter::class)
            ->where('rateable_id', $transporterId);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
