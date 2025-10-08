<?php

// app/Models/Offer.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\OfferStatus;

class Offer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'producer_id',
        'product_id',
        'location_id',
        'title',
        'description',
        'quantity_available',
        'quantity_reserved',
        'min_order_quantity',
        'price_per_unit',
        'harvest_date',
        'available_from',
        'available_until',
        'photos',
        'quality_grade',
        'organic',
        'status',
        'views_count',
    ];

    protected $casts = [
        'quantity_available' => 'decimal:2',
        'quantity_reserved' => 'decimal:2',
        'min_order_quantity' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'harvest_date' => 'date',
        'available_from' => 'date',
        'available_until' => 'date',
        'photos' => 'array',
        'organic' => 'boolean',
        'status' => OfferStatus::class,
    ];

    // Relations
    public function producer()
    {
        return $this->belongsTo(Producer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
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
    public function getRemainingQuantityAttribute(): float
    {
        return $this->quantity_available - $this->quantity_reserved;
    }

    public function getPhotoUrlsAttribute(): array
    {
        if (!$this->photos) {
            return [];
        }

        return array_map(function($photo) {
            return url('storage/' . $photo);
        }, $this->photos);
    }

    public function canOrder(float $quantity): bool
    {
        return $this->status === OfferStatus::ACTIVE 
            && $this->remaining_quantity >= $quantity
            && (!$this->min_order_quantity || $quantity >= $this->min_order_quantity)
            && $this->available_from <= now()
            && $this->available_until >= now();
    }

    public function reserveQuantity(float $quantity): void
    {
        $this->increment('quantity_reserved', $quantity);
        $this->updateStatus();
    }

    public function releaseQuantity(float $quantity): void
    {
        $this->decrement('quantity_reserved', $quantity);
        $this->updateStatus();
    }

    public function updateStatus(): void
    {
        if ($this->remaining_quantity <= 0) {
            $this->update(['status' => OfferStatus::SOLD_OUT]);
        } elseif ($this->remaining_quantity < $this->quantity_available) {
            $this->update(['status' => OfferStatus::RESERVED]);
        } elseif ($this->available_until < now()) {
            $this->update(['status' => OfferStatus::EXPIRED]);
        }
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', OfferStatus::ACTIVE)
            ->where('available_from', '<=', now())
            ->where('available_until', '>=', now());
    }

    public function scopeByLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhereHas('product', function($query) use ($search) {
                  $query->where('name', 'like', "%{$search}%");
              });
        });
    }
}
