<?php

// app/Enums/TransporterCertificationLevel.php
namespace App\Enums;

enum TransporterCertificationLevel: string
{
    case NONE = 'none';
    case BRONZE = 'bronze';
    case SILVER = 'silver';
    case GOLD = 'gold';
    case PLATINUM = 'platinum';

    public function label(): string
    {
        return match($this) {
            self::NONE => 'Non certifié',
            self::BRONZE => 'Bronze',
            self::SILVER => 'Argent',
            self::GOLD => 'Or',
            self::PLATINUM => 'Platine',
        };
    }

    public function minRating(): float
    {
        return match($this) {
            self::NONE => 0.0,
            self::BRONZE => 3.5,
            self::SILVER => 4.0,
            self::GOLD => 4.5,
            self::PLATINUM => 4.8,
        };
    }

    public function minDeliveries(): int
    {
        return match($this) {
            self::NONE => 0,
            self::BRONZE => 10,
            self::SILVER => 50,
            self::GOLD => 200,
            self::PLATINUM => 500,
        };
    }

    public function bonusPercentage(): int
    {
        return match($this) {
            self::NONE => 0,
            self::BRONZE => 5,
            self::SILVER => 10,
            self::GOLD => 15,
            self::PLATINUM => 20,
        };
    }
}