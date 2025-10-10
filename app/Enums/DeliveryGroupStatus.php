<?php

// app/Enums/DeliveryGroupStatus.php
namespace App\Enums;

enum DeliveryGroupStatus: string
{
    case PENDING = 'pending';
    case ASSIGNED = 'assigned';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::ASSIGNED => 'Assigné',
            self::IN_PROGRESS => 'En cours',
            self::COMPLETED => 'Terminé',
            self::FAILED => 'Échoué',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::ASSIGNED => 'info',
            self::IN_PROGRESS => 'primary',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
        };
    }
}