<?php

// app/Traits/Auditable.php
namespace App\Traits;

use App\Models\ActivityLog;

trait Auditable
{
    /**
     * Boot du trait
     */
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->logActivity('created');
        });

        static::updated(function ($model) {
            $model->logActivity('updated', $model->getChanges());
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });
    }

    /**
     * Relation vers logs d'activité
     */
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }

    /**
     * Logger une activité
     */
    public function logActivity(string $action, array $changes = []): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model' => get_class($this),
            'model_id' => $this->id,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Obtenir historique des modifications
     */
    public function getHistory(int $limit = 10)
    {
        return $this->activityLogs()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}