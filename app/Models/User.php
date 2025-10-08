<?php

// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\UserRole;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

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

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function givenRatings()
    {
        return $this->hasMany(Rating::class, 'rater_id');
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
            return null;
        }
        return url('storage/' . $this->profile_photo);
    }
}
