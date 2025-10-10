<?php

// app/Models/Order.php (MODIFIÉ COMPLET - NOUVELLE VERSION)
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\OrderStatus;
use App\Traits\HasStatus;
use App\Traits\Auditable;
use App\Traits\HasTimeframes;

class Order extends Model
{
    use HasFactory, SoftDeletes, HasStatus, Auditable, HasTimeframes;

    protected $fillable = [
        'order_number',
        'buyer_id',
        'subtotal',
        'platform_commission',
        'delivery_cost',
        'total_amount',
        'delivery_address',
        'delivery_location_id',
        'requested_delivery_date',
        'delivery_notes',
        'total_items',
        'items_confirmed',
        'items_cancelled',
        'is_multi_producer',
        'status',
        'cancellation_reason',
        'confirmed_at',
        'paid_at',
        'delivered_at',
        'completed_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'requested_delivery_date' => 'datetime',
        'is_multi_producer' => 'boolean',
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

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function producers()
    {
        return $this->belongsToMany(Producer::class, 'order_items')
            ->withPivot(['quantity', 'unit_price', 'subtotal', 'status'])
            ->distinct();
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

    // Boot
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
        $this->subtotal = $this->items->sum('subtotal');
        $this->platform_commission = $this->items->sum('platform_commission');
        $this->total_amount = $this->subtotal + $this->delivery_cost;
        $this->total_items = $this->items->count();
        $this->is_multi_producer = $this->items->pluck('producer_id')->unique()->count() > 1;
        
        $this->save();
    }

    public function recalculateItemsStatus(): void
    {
        $this->items_confirmed = $this->items()->confirmed()->count();
        $this->items_cancelled = $this->items()->where('status', 'cancelled')->count();
        
        $totalItems = $this->total_items;
        
        // Mettre à jour statut global selon items
        if ($this->items_cancelled === $totalItems && $totalItems > 0) {
            $this->status = OrderStatus::CANCELLED;
        } elseif ($this->items_confirmed === $totalItems && $totalItems > 0) {
            $this->status = OrderStatus::CONFIRMED;
        }
        
        $this->save();
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

        // Mettre à jour statistiques acheteur
        $this->buyer->incrementSpent($this->total_amount);

        // Libérer paiements aux producteurs
        if ($this->payment && $this->payment->status === \App\Enums\PaymentStatus::HELD) {
            // Sera géré par le PaymentService
            event(new \App\Events\Orders\OrderCompleted($this));
        }
    }

    public function cancel(string $reason): void
    {
        $this->update([
            'status' => OrderStatus::CANCELLED,
            'cancellation_reason' => $reason,
        ]);

        // Libérer quantités réservées
        $this->items->each(function($item) {
            if ($item->status->canBeCancelled()) {
                $item->cancel('Commande annulée');
            }
        });
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

    public function isWithinGuaranteedTime(): bool
    {
        if (!$this->confirmed_at || !$this->delivered_at) {
            return false;
        }

        $guaranteedHours = config('agri-connect.delivery_guarantee_hours', 48);
        $deliveryTime = $this->confirmed_at->diffInHours($this->delivered_at);
        
        return $deliveryTime <= $guaranteedHours;
    }

    public function getItemsProgressAttribute(): array
    {
        return [
            'total' => $this->total_items,
            'confirmed' => $this->items_confirmed,
            'cancelled' => $this->items_cancelled,
            'pending' => $this->total_items - $this->items_confirmed - $this->items_cancelled,
            'percentage_confirmed' => $this->total_items > 0 
                ? round(($this->items_confirmed / $this->total_items) * 100, 1)
                : 0,
        ];
    }

    // Scopes
    public function scopeForBuyer($query, $buyerId)
    {
        return $query->where('buyer_id', $buyerId);
    }

    public function scopeForProducer($query, $producerId)
    {
        return $query->whereHas('items', function($q) use ($producerId) {
            $q->where('producer_id', $producerId);
        });
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

    public function scopeCompleted($query)
    {
        return $query->where('status', OrderStatus::COMPLETED);
    }

    public function scopeMultiProducer($query)
    {
        return $query->where('is_multi_producer', true);
    }
}

