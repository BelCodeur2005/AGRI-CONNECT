<?php

// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Enums\ProductUnit;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'image',
        'unit',
        'is_perishable',
        'shelf_life_days',
        'quality_criteria',
        'is_active',
    ];

    protected $casts = [
        'unit' => ProductUnit::class,
        'is_perishable' => 'boolean',
        'quality_criteria' => 'array',
        'is_active' => 'boolean',
    ];

    // Relations
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function activeOffers()
    {
        return $this->offers()->where('status', 'active');
    }

    public function marketPrices()
    {
        return $this->hasMany(MarketPrice::class);
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }
        return url('storage/' . $this->image);
    }

    // Helper methods
    public function getCurrentMarketPrice($locationId = null)
    {
        $query = $this->marketPrices()
            ->whereDate('price_date', today());

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        return $query->first();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}

