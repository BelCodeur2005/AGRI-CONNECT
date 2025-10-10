<?php

// app/Enums/OrderItemStatus.php
namespace App\Enums;

enum OrderItemStatus: string
{
    case PENDING = 'pending';           // En attente confirmation producteur
    case CONFIRMED = 'confirmed';       // Confirmé par producteur
    case CANCELLED = 'cancelled';       // Annulé
    case READY = 'ready';              // Prêt pour collecte
    case COLLECTED = 'collected';      // Collecté par transporteur
    case DELIVERED = 'delivered';      // Livré
    case COMPLETED = 'completed';      // Confirmé par acheteur

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::CONFIRMED => 'Confirmé',
            self::CANCELLED => 'Annulé',
            self::READY => 'Prêt',
            self::COLLECTED => 'Collecté',
            self::DELIVERED => 'Livré',
            self::COMPLETED => 'Terminé',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::CONFIRMED, self::READY => 'info',
            self::COLLECTED => 'primary',
            self::DELIVERED, self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    public function isActive(): bool
    {
        return !in_array($this, [self::CANCELLED, self::COMPLETED]);
    }

    public function canBeConfirmed(): bool
    {
        return $this === self::PENDING;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED]);
    }
}

