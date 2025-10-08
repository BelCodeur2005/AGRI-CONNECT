<?php

// app/Models/PhoneVerification.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'code',
        'expires_at',
        'is_verified',
        'verified_at',
        'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    // Helper methods
    public static function generate(string $phone): self
    {
        // Invalider les anciens codes
        self::where('phone', $phone)
            ->where('is_verified', false)
            ->update(['is_verified' => true]);

        // Générer nouveau code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        return self::create([
            'phone' => $phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    public function verify(string $code): bool
    {
        $this->increment('attempts');

        if ($this->attempts > 5) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        if ($this->code === $code) {
            $this->update([
                'is_verified' => true,
                'verified_at' => now(),
            ]);
            return true;
        }

        return false;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_verified', false)
            ->where('expires_at', '>', now());
    }
}
