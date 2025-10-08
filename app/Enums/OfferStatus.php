<?php
// app/Enums/OfferStatus.php
namespace App\Enums;

enum OfferStatus: string
{
    case ACTIVE = 'active';         // Disponible
    case RESERVED = 'reserved';     // Réservé partiellement
    case SOLD_OUT = 'sold_out';     // Épuisé
    case EXPIRED = 'expired';       // Expiré
    case INACTIVE = 'inactive';     // Désactivé par producteur

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Disponible',
            self::RESERVED => 'Réservé',
            self::SOLD_OUT => 'Épuisé',
            self::EXPIRED => 'Expiré',
            self::INACTIVE => 'Inactif',
        };
    }
}