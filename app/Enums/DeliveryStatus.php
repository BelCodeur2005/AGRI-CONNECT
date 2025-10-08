<?php
// app/Enums/DeliveryStatus.php
namespace App\Enums;

enum DeliveryStatus: string
{
    case PENDING = 'pending';           // Pas encore assigné
    case ASSIGNED = 'assigned';         // Transporteur assigné
    case PICKED_UP = 'picked_up';       // Produit collecté
    case IN_TRANSIT = 'in_transit';     // En route
    case ARRIVED = 'arrived';           // Arrivé à destination
    case DELIVERED = 'delivered';       // Livré et confirmé
    case FAILED = 'failed';             // Échec livraison

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::ASSIGNED => 'Assigné',
            self::PICKED_UP => 'Collecté',
            self::IN_TRANSIT => 'En transit',
            self::ARRIVED => 'Arrivé',
            self::DELIVERED => 'Livré',
            self::FAILED => 'Échec',
        };
    }
}