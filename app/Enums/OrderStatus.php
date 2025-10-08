<?php
// app/Enums/OrderStatus.php
namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';           // Commande créée, attend confirmation producteur
    case CONFIRMED = 'confirmed';       // Producteur a accepté
    case PAYMENT_PENDING = 'payment_pending'; // Attend paiement acheteur
    case PAID = 'paid';                 // Payé, attend collecte
    case READY_FOR_PICKUP = 'ready_for_pickup'; // Prêt à être collecté
    case IN_TRANSIT = 'in_transit';     // En cours de livraison
    case DELIVERED = 'delivered';       // Livré, attend confirmation
    case COMPLETED = 'completed';       // Confirmé par acheteur, paiement libéré
    case CANCELLED = 'cancelled';       // Annulé
    case REFUNDED = 'refunded';         // Remboursé

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::CONFIRMED => 'Confirmé',
            self::PAYMENT_PENDING => 'Paiement en attente',
            self::PAID => 'Payé',
            self::READY_FOR_PICKUP => 'Prêt pour collecte',
            self::IN_TRANSIT => 'En transit',
            self::DELIVERED => 'Livré',
            self::COMPLETED => 'Terminé',
            self::CANCELLED => 'Annulé',
            self::REFUNDED => 'Remboursé',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::CONFIRMED, self::PAID => 'info',
            self::IN_TRANSIT => 'primary',
            self::DELIVERED, self::COMPLETED => 'success',
            self::CANCELLED, self::REFUNDED => 'danger',
            default => 'secondary',
        };
    }
}