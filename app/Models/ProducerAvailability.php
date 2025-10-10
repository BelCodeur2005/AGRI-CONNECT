<?php

// app/Models/ProducerAvailability.php (NOUVEAU)
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProducerAvailability extends Model
{
    use HasFactory;

    protected $table = 'producer_availability';

    protected $fillable = [
        'producer_id',
        'date',
        'available_from',
        'available_to',
        'is_available',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'available_from' => 'datetime:H:i',
        'available_to' => 'datetime:H:i',
        'is_available' => 'boolean',
    ];

    // Relations
    public function producer()
    {
        return $this->belongsTo(Producer::class);
    }

    // Helper methods
    public function isAvailableAt(\DateTime $time): bool
    {
        if (!$this->is_available) {
            return false;
        }

        if (!$this->available_from || !$this->available_to) {
            return $this->is_available;
        }

        $checkTime = $time->format('H:i');
        return $checkTime >= $this->available_from->format('H:i') 
            && $checkTime <= $this->available_to->format('H:i');
    }

    // Scopes
    public function scopeForProducer($query, $producerId)
    {
        return $query->where('producer_id', $producerId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', today());
    }
}
