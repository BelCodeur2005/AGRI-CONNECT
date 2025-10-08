<?php

// app/Models/Order.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\OrderStatus;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'buyer_id',
        'producer_id',
        'offer_id',
        'quantity',
        'unit_price',
        'subtotal',
        'platform_commission',
        'delivery_cost',
        'total_amount',
        'delivery_address',
        'delivery_location_id',
        'requested_delivery_date',
        'delivery_notes',
        'status',
        'cancellation_reason',
        'confirmed_at',
        'paid_at',
        'delivered_at',
        'completed_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'requested_delivery_date' => 'datetime',
        'status' => OrderStatus::class,
        'confirmed_at' => 'datetime',
        'paid_at' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relations
    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function producer()
    {
        return $this->belongsTo(Producer::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function deliveryLocation()
    {
        return $this->belongsTo(Location::class, 'delivery_location_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    // Helper methods
    public static function generateOrderNumber(): string
    {
        $prefix = 'AG';
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', today())->latest('id')->first();
        $sequence = $lastOrder ? (int)substr($lastOrder->order_number, -4) + 1 : 1;
        
        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->quantity * $this->unit_price;
        
        // Commission plateforme (défaut 7%)
        $commissionRate = config('agri-connect.platform_commission', 7) / 100;
        $this->platform_commission = $this->subtotal * $commissionRate;
        
        // Total
        $this->total_amount = $this->subtotal + $this->delivery_cost;
    }

    public function getProducerEarningsAttribute(): float
    {
        return $this->subtotal - $this->platform_commission;
    }

    public function confirm(): void
    {
        $this->update([
            'status' => OrderStatus::CONFIRMED,
            'confirmed_at' => now(),
        ]);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => OrderStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => OrderStatus::DELIVERED,
            'delivered_at' => now(),
        ]);
    }

    public function complete(): void
    {
        $this->update([
            'status' => OrderStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        // Mettre à jour statistiques
        $this->buyer->incrementSpent($this->total_amount);
        $this->producer->incrementRevenue($this->producer_earnings);
    }

    public function cancel(string $reason): void
    {
        $this->update([
            'status' => OrderStatus::CANCELLED,
            'cancellation_reason' => $reason,
        ]);

        // Libérer quantité réservée
        $this->offer->releaseQuantity($this->quantity);
    }

    public function isWithinGuaranteedTime(): bool
    {
        if (!$this->confirmed_at || !$this->delivered_at) {
            return false;
        }

        $guaranteedHours = config('agri-connect.delivery_guarantee_hours', 48);
        $deliveryTime = $this->confirmed_at->diffInHours($this->delivered_at);
        
        return $deliveryTime <= $guaranteedHours;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            OrderStatus::PENDING,
            OrderStatus::CONFIRMED,
            OrderStatus::PAYMENT_PENDING,
        ]);
    }

    public function canBeRated(): bool
    {
        return $this->status === OrderStatus::COMPLETED 
            && !$this->ratings()->where('rater_id', auth()->id())->exists();
    }

    // Scopes
    public function scopeForBuyer($query, $buyerId)
    {
        return $query->where('buyer_id', $buyerId);
    }

    public function scopeForProducer($query, $producerId)
    {
        return $query->where('producer_id', $producerId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', OrderStatus::PENDING);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            OrderStatus::CONFIRMED,
            OrderStatus::PAID,
            OrderStatus::READY_FOR_PICKUP,
            OrderStatus::IN_TRANSIT,
        ]);
    }
}

