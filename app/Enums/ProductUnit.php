<?php

// app/Enums/ProductUnit.php
namespace App\Enums;

enum ProductUnit: string
{
    case KG = 'kg';
    case TONNE = 'tonne';
    case LITRE = 'litre';
    case PIECE = 'piece';
    case BAG = 'bag';               // Sac
    case CRATE = 'crate';           // Caisse
    case BUNCH = 'bunch';           // Régime (plantain)

    public function label(): string
    {
        return match($this) {
            self::KG => 'Kilogramme',
            self::TONNE => 'Tonne',
            self::LITRE => 'Litre',
            self::PIECE => 'Pièce',
            self::BAG => 'Sac',
            self::CRATE => 'Caisse',
            self::BUNCH => 'Régime',
        };
    }

    public function shortLabel(): string
    {
        return match($this) {
            self::KG => 'kg',
            self::TONNE => 't',
            self::LITRE => 'L',
            self::PIECE => 'pcs',
            self::BAG => 'sac',
            self::CRATE => 'caisse',
            self::BUNCH => 'régime',
        };
    }
}