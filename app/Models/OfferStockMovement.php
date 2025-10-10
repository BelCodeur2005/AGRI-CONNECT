<?php

// app/Models/OfferStockMovement.php (NOUVEAU)
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\StockMovementType;

class OfferStockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_id',
        'order_item_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'type' => StockMovementType::class,
        'quantity' => 'decimal:2',
        'quantity_before' => 'decimal:2',
        'quantity_after' => 'decimal:2',
    ];

    // Relations
    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeForOffer($query, $offerId)
    {
        return $query->where('offer_id', $offerId);
    }

    public function scopeByType($query, StockMovementType $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
