<?php

// app/Models/CartItem.php (NOUVEAU - COMPLET)
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\Catalog\OfferExpiredException;
use App\Exceptions\Orders\InsufficientStockException;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'offer_id',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    protected $with = ['offer.product', 'offer.producer.user'];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    // Computed attributes
    public function getSubtotalAttribute(): float
    {
        return round($this->quantity * $this->offer->price_per_unit, 2);
    }

    public function getIsValidAttribute(): bool
    {
        return $this->offer->canOrder($this->quantity);
    }

    public function getStatusMessageAttribute(): ?string
    {
        if (!$this->offer->status->isActive()) {
            return 'Offre non disponible';
        }

        if ($this->offer->remaining_quantity < $this->quantity) {
            return 'Stock insuffisant';
        }

        if ($this->offer->available_until < now()) {
            return 'Offre expirée';
        }

        return null;
    }

    // Business methods
    public function updateQuantity(float $quantity): void
    {
        if ($quantity <= 0) {
            $this->delete();
            return;
        }

        if (!$this->offer->canOrder($quantity)) {
            throw new InsufficientStockException(
                $this->offer_id,
                $quantity,
                $this->offer->remaining_quantity
            );
        }

        $this->update(['quantity' => $quantity]);
    }

    public function validateBeforeCheckout(): void
    {
        // Vérifier offre toujours active
        if (!$this->offer->status->isActive()) {
            throw new OfferExpiredException($this->offer_id);
        }

        // Vérifier stock disponible
        if (!$this->offer->canOrder($this->quantity)) {
            throw new InsufficientStockException(
                $this->offer_id,
                $this->quantity,
                $this->offer->remaining_quantity
            );
        }
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeValid($query)
    {
        return $query->whereHas('offer', function($q) {
            $q->where('status', 'active')
              ->where('available_until', '>=', now());
        });
    }
}