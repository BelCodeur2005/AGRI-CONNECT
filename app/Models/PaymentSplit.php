<?php

// app/Models/PaymentSplit.php (NOUVEAU)
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\PaymentStatus;

class PaymentSplit extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'producer_id',
        'amount',
        'platform_commission',
        'net_amount',
        'status',
        'transaction_reference',
        'released_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'status' => PaymentStatus::class,
        'released_at' => 'datetime',
    ];

    // Relations
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function producer()
    {
        return $this->belongsTo(Producer::class);
    }

    // Business methods
    public function release(string $transactionRef = null): void
    {
        $this->update([
            'status' => PaymentStatus::RELEASED,
            'transaction_reference' => $transactionRef,
            'released_at' => now(),
        ]);

        // Mettre à jour revenus producteur
        $this->producer->incrementRevenue($this->net_amount);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => PaymentStatus::FAILED]);
    }

    // Scopes
    public function scopeForProducer($query, $producerId)
    {
        return $query->where('producer_id', $producerId);
    }

    public function scopeHeld($query)
    {
        return $query->where('status', PaymentStatus::HELD);
    }

    public function scopeReleased($query)
    {
        return $query->where('status', PaymentStatus::RELEASED);
    }
}
