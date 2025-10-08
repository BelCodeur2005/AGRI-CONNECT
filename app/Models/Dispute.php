<?php

// app/Models/Dispute.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'reported_by',
        'reported_against',
        'type',
        'description',
        'evidence_photos',
        'status',
        'admin_notes',
        'resolution',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'evidence_photos' => 'array',
        'resolved_at' => 'datetime',
    ];

    // Relations
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_against');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Helper methods
    public function resolve(User $admin, string $resolution): void
    {
        $this->update([
            'status' => 'resolved',
            'resolution' => $resolution,
            'resolved_by' => $admin->id,
            'resolved_at' => now(),
        ]);
    }

    public function getEvidencePhotoUrlsAttribute(): array
    {
        if (!$this->evidence_photos) {
            return [];
        }

        return array_map(function($photo) {
            return url('storage/' . $photo);
        }, $this->evidence_photos);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeForOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }
}
