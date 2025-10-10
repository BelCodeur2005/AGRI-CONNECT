<?php

// app/Models/DeliveryGroup.php (NOUVEAU)
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\DeliveryGroupStatus;
use App\Traits\HasStatus;

class DeliveryGroup extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'group_number',
        'transporter_id',
        'delivery_location_id',
        'delivery_address',
        'scheduled_date',
        'scheduled_time_from',
        'scheduled_time_to',
        'status',
        'total_orders',
        'total_producers',
        'total_weight',
        'total_delivery_cost',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time_from' => 'datetime:H:i',
        'scheduled_time_to' => 'datetime:H:i',
        'status' => DeliveryGroupStatus::class,
        'total_weight' => 'decimal:2',
        'total_delivery_cost' => 'decimal:2',
    ];

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($group) {
            if (!$group->group_number) {
                $group->group_number = self::generateGroupNumber();
            }
        });
    }

    // Relations
    public function transporter()
    {
        return $this->belongsTo(Transporter::class);
    }

    public function deliveryLocation()
    {
        return $this->belongsTo(Location::class, 'delivery_location_id');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, Delivery::class);
    }

    // Helper methods
    public static function generateGroupNumber(): string
    {
        $prefix = 'DG';
        $date = now()->format('Ymd');
        $last = self::whereDate('created_at', today())->latest('id')->first();
        $sequence = $last ? (int)substr($last->group_number, -4) + 1 : 1;
        
        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    public function calculateTotals(): void
    {
        $this->total_orders = $this->deliveries()->count();
        $this->total_producers = $this->deliveries()
            ->with('order.items')
            ->get()
            ->pluck('order.items.*.producer_id')
            ->flatten()
            ->unique()
            ->count();
            
        $this->total_weight = $this->deliveries()
            ->with('order.items')
            ->get()
            ->sum(function($delivery) {
                return $delivery->order->items->sum('quantity');
            });

        $this->total_delivery_cost = $this->deliveries()->sum('delivery_cost');
        
        $this->save();
    }

    public function assignToTransporter(Transporter $transporter): void
    {
        $this->update([
            'transporter_id' => $transporter->id,
            'status' => DeliveryGroupStatus::ASSIGNED,
        ]);

        // Assigner toutes les livraisons du groupe
        $this->deliveries()->each(function($delivery) use ($transporter) {
            $delivery->assignToTransporter($transporter);
        });
    }

    // Scopes
    public function scopeForTransporter($query, $transporterId)
    {
        return $query->where('transporter_id', $transporterId);
    }

    public function scopeScheduledFor($query, $date)
    {
        return $query->whereDate('scheduled_date', $date);
    }

    public function scopePending($query)
    {
        return $query->where('status', DeliveryGroupStatus::PENDING);
    }
}