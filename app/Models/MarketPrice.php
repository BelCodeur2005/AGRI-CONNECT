<?php

// app/Models/MarketPrice.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'location_id',
        'min_price',
        'max_price',
        'avg_price',
        'suggested_price',
        'price_date',
        'source',
    ];

    protected $casts = [
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'avg_price' => 'decimal:2',
        'suggested_price' => 'decimal:2',
        'price_date' => 'date',
    ];

    // Relations
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('price_date', today());
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }
}
