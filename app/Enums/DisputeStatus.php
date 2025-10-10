<?php

// app/Enums/DisputeStatus.php
namespace App\Enums;

enum DisputeStatus: string
{
    case OPEN = 'open';                 // Ouvert
    case INVESTIGATING = 'investigating'; // En cours d'investigation
    case PENDING_EVIDENCE = 'pending_evidence'; // En attente de preuves
    case RESOLVED = 'resolved';         // Résolu
    case CLOSED = 'closed';            // Fermé
    case ESCALATED = 'escalated';      // Escaladé à l'admin

    public function label(): string
    {
        return match($this) {
            self::OPEN => 'Ouvert',
            self::INVESTIGATING => 'En investigation',
            self::PENDING_EVIDENCE => 'En attente de preuves',
            self::RESOLVED => 'Résolu',
            self::CLOSED => 'Fermé',
            self::ESCALATED => 'Escaladé',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::OPEN => 'danger',
            self::INVESTIGATING, self::PENDING_EVIDENCE => 'warning',
            self::ESCALATED => 'dark',
            self::RESOLVED, self::CLOSED => 'success',
        };
    }

    public function isActive(): bool
    {
        return !in_array($this, [self::RESOLVED, self::CLOSED]);
    }
}
