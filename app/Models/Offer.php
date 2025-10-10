<?php

// app/Models/Offer.php (MODIFIÉ - avec stock movements)
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\OfferStatus;
use App\Enums\StockMovementType;
use App\Traits\HasStatus;
use App\Traits\Searchable;
use App\Traits\HasTimeframes;

class Offer extends Model
{
    use HasFactory, SoftDeletes, HasStatus, Searchable, HasTimeframes;

    protected $searchable = ['title', 'description'];

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

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    public function stockMovements()
    {
        return $this->hasMany(OfferStockMovement::class);
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

    public function reserveQuantity(float $quantity, ?OrderItem $orderItem = null): void
    {
        $quantityBefore = $this->remaining_quantity;
        
        $this->increment('quantity_reserved', $quantity);
        
        // Créer mouvement stock
        $this->stockMovements()->create([
            'order_item_id' => $orderItem?->id,
            'type' => StockMovementType::RESERVATION,
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $this->fresh()->remaining_quantity,
            'reason' => 'Réservation commande',
            'created_by' => auth()->id(),
        ]);
        
        $this->updateStatus();
    }

    public function releaseQuantity(float $quantity, ?OrderItem $orderItem = null): void
    {
        $quantityBefore = $this->remaining_quantity;
        
        $this->decrement('quantity_reserved', $quantity);
        
        // Créer mouvement stock
        $this->stockMovements()->create([
            'order_item_id' => $orderItem?->id,
            'type' => StockMovementType::RELEASE,
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $this->fresh()->remaining_quantity,
            'reason' => 'Annulation commande',
            'created_by' => auth()->id(),
        ]);
        
        $this->updateStatus();
    }

    public function confirmSale(float $quantity, ?OrderItem $orderItem = null): void
    {
        $quantityBefore = $this->quantity_available;
        
        // Réduire stock total
        $this->decrement('quantity_available', $quantity);
        $this->decrement('quantity_reserved', $quantity);
        
        // Créer mouvement stock
        $this->stockMovements()->create([
            'order_item_id' => $orderItem?->id,
            'type' => StockMovementType::SALE,
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $this->fresh()->quantity_available,
            'reason' => 'Vente confirmée',
            'created_by' => auth()->id(),
        ]);
        
        $this->updateStatus();
    }

    public function updateStatus(): void
    {
        $this->refresh();
        
        if ($this->remaining_quantity <= 0) {
            $this->update(['status' => OfferStatus::SOLD_OUT]);
        } elseif ($this->remaining_quantity < $this->quantity_available) {
            $this->update(['status' => OfferStatus::RESERVED]);
        } elseif ($this->available_until < now()) {
            $this->update(['status' => OfferStatus::EXPIRED]);
        } elseif ($this->status !== OfferStatus::ACTIVE && $this->remaining_quantity > 0) {
            $this->update(['status' => OfferStatus::ACTIVE]);
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

    public function scopeByProducer($query, $producerId)
    {
        return $query->where('producer_id', $producerId);
    }

    public function scopeOrganic($query)
    {
        return $query->where('organic', true);
    }

    public function scopeExpiringWithin($query, int $days)
    {
        return $query->where('available_until', '<=', now()->addDays($days))
            ->where('available_until', '>=', now());
    }

    public function scopeLowStock($query, float $threshold = 10)
    {
        return $query->whereRaw('(quantity_available - quantity_reserved) < ?', [$threshold]);
    }
}