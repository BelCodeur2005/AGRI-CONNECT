<?php

// app/Models/User.php (MODIFIÉ - avec traits et méthodes)
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\UserRole;
use App\Traits\HasNotifications;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasNotifications;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'profile_photo',
        'is_active',
        'is_verified',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'phone_verified_at' => 'datetime',
        'role' => UserRole::class,
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    // Relations
    public function producer()
    {
        return $this->hasOne(Producer::class);
    }

    public function buyer()
    {
        return $this->hasOne(Buyer::class);
    }

    public function transporter()
    {
        return $this->hasOne(Transporter::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class, 'reported_by');
    }

    public function phoneVerifications()
    {
        return $this->hasMany(PhoneVerification::class, 'phone', 'phone');
    }

    // Helper methods
    public function isProducer(): bool
    {
        return $this->role === UserRole::PRODUCER;
    }

    public function isBuyer(): bool
    {
        return $this->role === UserRole::BUYER;
    }

    public function isTransporter(): bool
    {
        return $this->role === UserRole::TRANSPORTER;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function getProfile()
    {
        return match($this->role) {
            UserRole::PRODUCER => $this->producer,
            UserRole::BUYER => $this->buyer,
            UserRole::TRANSPORTER => $this->transporter,
            default => null,
        };
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (!$this->profile_photo) {
            return $this->getDefaultAvatar();
        }
        return url('storage/' . $this->profile_photo);
    }

    public function getDefaultAvatar(): string
    {
        // Générer avatar avec initiales
        $initials = strtoupper(substr($this->name, 0, 2));
        return "https://ui-avatars.com/api/?name={$initials}&size=200&background=10b981&color=fff";
    }

    public function hasCompleteProfile(): bool
    {
        if (!$this->phone_verified_at) {
            return false;
        }

        $profile = $this->getProfile();
        
        if (!$profile) {
            return false;
        }

        return match($this->role) {
            UserRole::PRODUCER => $profile->location_id && $profile->farm_address,
            UserRole::BUYER => $profile->location_id && $profile->business_name && $profile->delivery_address,
            UserRole::TRANSPORTER => $profile->vehicle_registration && $profile->driver_license_number,
            default => true,
        };
    }

    public function getMissingProfileFields(): array
    {
        $missing = [];

        if (!$this->phone_verified_at) {
            $missing[] = 'phone_verification';
        }

        $profile = $this->getProfile();
        
        if (!$profile) {
            return ['profile_not_created'];
        }

        if ($this->isProducer()) {
            if (!$profile->location_id) $missing[] = 'location';
            if (!$profile->farm_address) $missing[] = 'farm_address';
        }

        if ($this->isBuyer()) {
            if (!$profile->location_id) $missing[] = 'location';
            if (!$profile->business_name) $missing[] = 'business_name';
            if (!$profile->delivery_address) $missing[] = 'delivery_address';
        }

        if ($this->isTransporter()) {
            if (!$profile->vehicle_registration) $missing[] = 'vehicle_registration';
            if (!$profile->driver_license_number) $missing[] = 'driver_license';
        }

        return $missing;
    }

    public function canAccessResource(string $resource): bool
    {
        $permissions = [
            UserRole::PRODUCER->value => ['offers', 'orders', 'payments', 'analytics'],
            UserRole::BUYER->value => ['cart', 'orders', 'payments', 'favorites'],
            UserRole::TRANSPORTER->value => ['deliveries', 'earnings', 'analytics'],
            UserRole::ADMIN->value => ['*'],
        ];

        $allowedResources = $permissions[$this->role->value] ?? [];

        return in_array('*', $allowedResources) || in_array($resource, $allowedResources);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true)
            ->whereNotNull('phone_verified_at');
    }

    public function scopeByRole($query, UserRole $role)
    {
        return $query->where('role', $role);
    }

    public function scopeProducers($query)
    {
        return $query->where('role', UserRole::PRODUCER);
    }

    public function scopeBuyers($query)
    {
        return $query->where('role', UserRole::BUYER);
    }

    public function scopeTransporters($query)
    {
        return $query->where('role', UserRole::TRANSPORTER);
    }
}
