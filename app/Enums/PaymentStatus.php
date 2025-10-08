<?php
// app/Enums/PaymentStatus.php
namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';       // En attente
    case HELD = 'held';             // Argent bloqué (escrow)
    case RELEASED = 'released';     // Libéré au producteur
    case REFUNDED = 'refunded';     // Remboursé à l'acheteur
    case FAILED = 'failed';         // Échec paiement

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::HELD => 'Bloqué',
            self::RELEASED => 'Libéré',
            self::REFUNDED => 'Remboursé',
            self::FAILED => 'Échoué',
        };
    }
}