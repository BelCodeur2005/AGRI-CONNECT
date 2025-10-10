<?php

// app/Enums/StockMovementType.php
namespace App\Enums;

enum StockMovementType: string
{
    case RESERVATION = 'reservation';   // Réservé lors de commande
    case RELEASE = 'release';          // Libéré après annulation
    case ADJUSTMENT = 'adjustment';     // Ajustement manuel
    case SALE = 'sale';                // Vente confirmée

    public function label(): string
    {
        return match($this) {
            self::RESERVATION => 'Réservation',
            self::RELEASE => 'Libération',
            self::ADJUSTMENT => 'Ajustement',
            self::SALE => 'Vente',
        };
    }

    public function affectsAvailableStock(): bool
    {
        return in_array($this, [
            self::RESERVATION,
            self::SALE,
        ]);
    }

    public function isDecrease(): bool
    {
        return in_array($this, [
            self::RESERVATION,
            self::SALE,
        ]);
    }
}
