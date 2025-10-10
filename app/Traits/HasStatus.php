<?php

// app/Traits/HasStatus.php
namespace App\Traits;

trait HasStatus
{
    /**
     * Obtenir label du statut
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    /**
     * Obtenir couleur du statut
     */
    public function getStatusColorAttribute(): string
    {
        return $this->status->color();
    }

    /**
     * Vérifier si statut actif
     */
    public function isStatusActive(): bool
    {
        return method_exists($this->status, 'isActive') 
            ? $this->status->isActive() 
            : true;
    }

    /**
     * Changer statut avec log
     */
    public function changeStatus($newStatus, string $reason = null): void
    {
        $oldStatus = $this->status;
        
        $this->update(['status' => $newStatus]);

        // Logger le changement si trait Auditable présent
        if (method_exists($this, 'logActivity')) {
            $this->logActivity('status_changed', [
                'old_status' => $oldStatus->value,
                'new_status' => $newStatus->value,
                'reason' => $reason,
            ]);
        }
    }

    /**
     * Scope par statut
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope statuts multiples
     */
    public function scopeWithStatuses($query, array $statuses)
    {
        return $query->whereIn('status', $statuses);
    }
}
