<?php

// app/Models/Payment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;

class Payment extends Model
{
    use HasFactory;

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
    }

    public function release(): void
    {
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

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', PaymentStatus::PENDING);
    }

    public function scopeHeld($query)
    {
        return $query->where('status', PaymentStatus::HELD);
    }
}