<?php

// app/Models/Location.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'region',
        'type',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    // Relations
    public function producers()
    {
        return $this->hasMany(Producer::class);
    }

    public function buyers()
    {
        return $this->hasMany(Buyer::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    // Helper methods
    public function getFullNameAttribute(): string
    {
        return $this->name . ', ' . $this->region;
    }
}