<?php

// app/Models/Payment.php (MODIFIÉ - avec splits)
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;
use App\Traits\HasStatus;

class Payment extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'order_id',
        'transaction_id',
        'amount',
        'method',
        'status',
        'payer_id',
        'payer_phone',
        'payee_id',
        'payee_phone',
        'payment_metadata',
        'operator_reference',
        'paid_at',
        'held_at',
        'released_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'method' => PaymentMethod::class,
        'status' => PaymentStatus::class,
        'payment_metadata' => 'array',
        'paid_at' => 'datetime',
        'held_at' => 'datetime',
        'released_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    // Relations
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function payee()
    {
        return $this->belongsTo(User::class, 'payee_id');
    }

    public function splits()
    {
        return $this->hasMany(PaymentSplit::class);
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->transaction_id) {
                $payment->transaction_id = self::generateTransactionId();
            }
        });
    }

    // Helper methods
    public static function generateTransactionId(): string
    {
        return 'TXN-' . now()->format('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));
    }

    public function hold(): void
    {
        $this->update([
            'status' => PaymentStatus::HELD,
            'held_at' => now(),
        ]);

        // Créer les splits pour chaque producteur
        $this->createSplits();
    }

    public function createSplits(): void
    {
        $order = $this->order;

        // Grouper items par producteur
        $itemsByProducer = $order->items->groupBy('producer_id');

        foreach ($itemsByProducer as $producerId => $items) {
            $totalAmount = $items->sum('subtotal');
            $totalCommission = $items->sum('platform_commission');
            $netAmount = $totalAmount - $totalCommission;

            $this->splits()->create([
                'producer_id' => $producerId,
                'amount' => $totalAmount,
                'platform_commission' => $totalCommission,
                'net_amount' => $netAmount,
                'status' => PaymentStatus::HELD,
            ]);
        }
    }

    public function releaseToProducers(): void
    {
        foreach ($this->splits as $split) {
            $split->release();
        }

        $this->update([
            'status' => PaymentStatus::RELEASED,
            'released_at' => now(),
        ]);
    }

    public function refund(): void
    {
        $this->update([
            'status' => PaymentStatus::REFUNDED,
            'refunded_at' => now(),
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update([
            'status' => PaymentStatus::FAILED,
        ]);
    }

    public function getTimeSinceHeldAttribute(): ?int
    {
        if (!$this->held_at) {
            return null;
        }

        return $this->held_at->diffInHours(now());
    }

    public function canBeReleased(): bool
    {
        return $this->status === PaymentStatus::HELD 
            && $this->order->status === \App\Enums\OrderStatus::COMPLETED;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', PaymentStatus::PENDING);
    }

    public function scopeHeld($query)
    {
        return $query->where('status', PaymentStatus::HELD);
    }

    public function scopeByMethod($query, PaymentMethod $method)
    {
        return $query->where('method', $method);
    }

    public function scopeForOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }
}
