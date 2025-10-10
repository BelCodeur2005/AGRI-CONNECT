<?php

// app/Traits/HasUuid.php
namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot du trait
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Scope par UUID
     */
    public function scopeByUuid($query, string $uuid)
    {
        return $query->where('uuid', $uuid);
    }

    /**
     * Trouver par UUID
     */
    public static function findByUuid(string $uuid)
    {
        return static::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Route binding par UUID
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}