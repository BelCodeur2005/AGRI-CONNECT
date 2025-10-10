<?php

// app/Models/OrderItem.php (NOUVEAU - COMPLET)
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\OrderItemStatus;
use App\Traits\HasStatus;
use App\Traits\Auditable;

class OrderItem extends Model
{
    use HasFactory, HasStatus, Auditable;

    protected $fillable = [
        'order_id',
        'offer_id',
        'producer_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'subtotal',
        'platform_commission',
        'status',
        'producer_notes',
        'cancellation_reason',
        'confirmed_at',
        'cancelled_at',
        'ready_at',
        'collected_at',
        'delivered_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        'status' => OrderItemStatus::class,
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'ready_at' => 'datetime',
        'collected_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Relations
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function producer()
    {
        return $this->belongsTo(Producer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(OfferStockMovement::class);
    }

    // Computed attributes
    public function getProducerEarningsAttribute(): float
    {
        return $this->subtotal - $this->platform_commission;
    }

    public function getIsConfirmedAttribute(): bool
    {
        return $this->status === OrderItemStatus::CONFIRMED;
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === OrderItemStatus::CANCELLED;
    }

    // Business methods
    public function confirm(): void
    {
        if (!$this->status->canBeConfirmed()) {
            throw new \Exception('Cet article ne peut pas être confirmé');
        }

        $this->update([
            'status' => OrderItemStatus::CONFIRMED,
            'confirmed_at' => now(),
        ]);
        
        $this->order->recalculateItemsStatus();
    }

    public function cancel(string $reason): void
    {
        if (!$this->status->canBeCancelled()) {
            throw new \Exception('Cet article ne peut plus être annulé');
        }

        $this->update([
            'status' => OrderItemStatus::CANCELLED,
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);
        
        // Libérer stock réservé
        $this->offer->releaseQuantity($this->quantity);
        $this->order->recalculateItemsStatus();
    }

    public function markAsReady(): void
    {
        $this->update([
            'status' => OrderItemStatus::READY,
            'ready_at' => now(),
        ]);
        
        $this->order->recalculateItemsStatus();
    }

    public function markAsCollected(): void
    {
        $this->update([
            'status' => OrderItemStatus::COLLECTED,
            'collected_at' => now(),
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => OrderItemStatus::DELIVERED,
            'delivered_at' => now(),
        ]);
    }

    public function complete(): void
    {
        $this->update([
            'status' => OrderItemStatus::COMPLETED,
        ]);
    }

    // Scopes
    public function scopeForProducer($query, $producerId)
    {
        return $query->where('producer_id', $producerId);
    }

    public function scopeForOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopePending($query)
    {
        return $query->where('status', OrderItemStatus::PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', OrderItemStatus::CONFIRMED);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            OrderItemStatus::CANCELLED,
            OrderItemStatus::COMPLETED,
        ]);
    }
}
