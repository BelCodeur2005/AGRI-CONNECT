<?php

// app/Models/Delivery.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\DeliveryStatus;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transporter_id',
        'pickup_location_id',
        'pickup_address',
        'delivery_location_id',
        'delivery_address',
        'scheduled_pickup_at',
        'scheduled_delivery_at',
        'actual_pickup_at',
        'actual_delivery_at',
        'status',
        'current_latitude',
        'current_longitude',
        'last_location_update',
        'delivery_notes',
        'delivery_proof_photo',
        'signature',
        'on_time',
        'delay_minutes',
    ];

    protected $casts = [
        'scheduled_pickup_at' => 'datetime',
        'scheduled_delivery_at' => 'datetime',
        'actual_pickup_at' => 'datetime',
        'actual_delivery_at' => 'datetime',
        'status' => DeliveryStatus::class,
        'current_latitude' => 'decimal:8',
        'current_longitude' => 'decimal:8',
        'last_location_update' => 'datetime',
        'on_time' => 'boolean',
    ];

    // Relations
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function transporter()
    {
        return $this->belongsTo(Transporter::class);
    }

    public function pickupLocation()
    {
        return $this->belongsTo(Location::class, 'pickup_location_id');
    }

    public function deliveryLocation()
    {
        return $this->belongsTo(Location::class, 'delivery_location_id');
    }

    // Helper methods
    public function assignToTransporter(Transporter $transporter): void
    {
        $this->update([
            'transporter_id' => $transporter->id,
            'status' => DeliveryStatus::ASSIGNED,
        ]);
    }

    public function markAsPickedUp(): void
    {
        $this->update([
            'status' => DeliveryStatus::PICKED_UP,
            'actual_pickup_at' => now(),
        ]);
    }

    public function startTransit(): void
    {
        $this->update([
            'status' => DeliveryStatus::IN_TRANSIT,
        ]);
    }

    public function markAsArrived(): void
    {
        $this->update([
            'status' => DeliveryStatus::ARRIVED,
        ]);
    }

    public function complete(string $proofPhoto = null, string $signature = null): void
    {
        $this->update([
            'status' => DeliveryStatus::DELIVERED,
            'actual_delivery_at' => now(),
            'delivery_proof_photo' => $proofPhoto,
            'signature' => $signature,
        ]);

        $this->calculateDelay();
    }

    public function updateLocation(float $latitude, float $longitude): void
    {
        $this->update([
            'current_latitude' => $latitude,
            'current_longitude' => $longitude,
            'last_location_update' => now(),
        ]);
    }

    public function calculateDelay(): void
    {
        if (!$this->scheduled_delivery_at || !$this->actual_delivery_at) {
            return;
        }

        $delayMinutes = $this->scheduled_delivery_at->diffInMinutes($this->actual_delivery_at, false);
        
        $this->update([
            'on_time' => $delayMinutes <= 0,
            'delay_minutes' => max(0, $delayMinutes),
        ]);
    }

    public function getDeliveryProofUrlAttribute(): ?string
    {
        if (!$this->delivery_proof_photo) {
            return null;
        }
        return url('storage/' . $this->delivery_proof_photo);
    }

    // Scopes
    public function scopeForTransporter($query, $transporterId)
    {
        return $query->where('transporter_id', $transporterId);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            DeliveryStatus::ASSIGNED,
            DeliveryStatus::PICKED_UP,
            DeliveryStatus::IN_TRANSIT,
        ]);
    }
}
