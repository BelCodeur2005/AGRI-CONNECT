<?php

// app/Traits/HasNotifications.php
namespace App\Traits;

use App\Models\Notification;
use App\Enums\NotificationType;

trait HasNotifications
{
    /**
     * Relation vers notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Notifications non lues
     */
    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }

    /**
     * Envoyer une notification
     */
    public function notify(NotificationType $type, string $title, string $body, array $data = []): Notification
    {
        return $this->notifications()->create([
            'type' => $type->value,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
    }

    /**
     * Marquer toutes comme lues
     */
    public function markAllNotificationsAsRead(): void
    {
        $this->unreadNotifications()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Nombre de notifications non lues
     */
    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Supprimer les anciennes notifications
     */
    public function cleanOldNotifications(int $daysOld = 30): void
    {
        $this->notifications()
            ->where('created_at', '<', now()->subDays($daysOld))
            ->where('is_read', true)
            ->delete();
    }
}