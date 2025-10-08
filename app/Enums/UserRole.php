<?php

// app/Enums/UserRole.php
namespace App\Enums;

enum UserRole: string
{
    case PRODUCER = 'producer';
    case BUYER = 'buyer';
    case TRANSPORTER = 'transporter';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match($this) {
            self::PRODUCER => 'Producteur',
            self::BUYER => 'Acheteur',
            self::TRANSPORTER => 'Transporteur',
            self::ADMIN => 'Administrateur',
        };
    }
}