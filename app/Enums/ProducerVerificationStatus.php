<?php

// app/Enums/ProducerVerificationStatus.php
namespace App\Enums;

enum ProducerVerificationStatus: string
{
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::VERIFIED => 'Vérifié',
            self::REJECTED => 'Rejeté',
            self::SUSPENDED => 'Suspendu',
        };
    }

    public function canCreateOffers(): bool
    {
        return $this === self::VERIFIED;
    }
}   