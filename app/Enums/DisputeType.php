<?php

// app/Enums/DisputeType.php
namespace App\Enums;

enum DisputeType: string
{
    case QUALITY = 'quality';           // Problème de qualité
    case QUANTITY = 'quantity';         // Quantité incorrecte
    case DELAY = 'delay';              // Retard de livraison
    case DAMAGE = 'damage';            // Produit endommagé
    case WRONG_PRODUCT = 'wrong_product'; // Mauvais produit livré
    case PAYMENT = 'payment';          // Problème de paiement
    case OTHER = 'other';              // Autre

    public function label(): string
    {
        return match($this) {
            self::QUALITY => 'Problème de qualité',
            self::QUANTITY => 'Quantité incorrecte',
            self::DELAY => 'Retard de livraison',
            self::DAMAGE => 'Produit endommagé',
            self::WRONG_PRODUCT => 'Mauvais produit',
            self::PAYMENT => 'Problème de paiement',
            self::OTHER => 'Autre',
        };
    }

    public function requiresEvidence(): bool
    {
        return in_array($this, [
            self::QUALITY,
            self::QUANTITY,
            self::DAMAGE,
            self::WRONG_PRODUCT,
        ]);
    }

    public function severity(): string
    {
        return match($this) {
            self::QUALITY, self::DAMAGE, self::WRONG_PRODUCT => 'high',
            self::QUANTITY, self::PAYMENT => 'medium',
            self::DELAY, self::OTHER => 'low',
        };
    }
}