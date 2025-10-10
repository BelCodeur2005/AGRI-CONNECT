<?php

// app/Enums/CartItemStatus.php
namespace App\Enums;

enum CartItemStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';         // Offre expirée
    case OUT_OF_STOCK = 'out_of_stock'; // Plus de stock
    case PRICE_CHANGED = 'price_changed'; // Prix changé

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Actif',
            self::EXPIRED => 'Expiré',
            self::OUT_OF_STOCK => 'Rupture de stock',
            self::PRICE_CHANGED => 'Prix modifié',
        };
    }

    public function canCheckout(): bool
    {
        return $this === self::ACTIVE;
    }
}