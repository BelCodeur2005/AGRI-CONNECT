<?php

// app/Enums/NotificationType.php
namespace App\Enums;

enum NotificationType: string
{
    // Auth
    case WELCOME = 'welcome';
    case PHONE_VERIFIED = 'phone_verified';
    case ACCOUNT_APPROVED = 'account_approved';
    
    // Orders
    case ORDER_CREATED = 'order_created';
    case ORDER_CONFIRMED = 'order_confirmed';
    case ORDER_CANCELLED = 'order_cancelled';
    case ORDER_COMPLETED = 'order_completed';
    
    // Order Items
    case ITEM_CONFIRMED = 'item_confirmed';
    case ITEM_CANCELLED = 'item_cancelled';
    case ITEM_READY = 'item_ready';
    
    // Payments
    case PAYMENT_RECEIVED = 'payment_received';
    case PAYMENT_RELEASED = 'payment_released';
    case PAYMENT_FAILED = 'payment_failed';
    
    // Deliveries
    case DELIVERY_ASSIGNED = 'delivery_assigned';
    case DELIVERY_STARTED = 'delivery_started';
    case DELIVERY_ARRIVED = 'delivery_arrived';
    case DELIVERY_COMPLETED = 'delivery_completed';
    case DELIVERY_DELAYED = 'delivery_delayed';
    
    // Ratings
    case NEW_RATING = 'new_rating';
    
    // Disputes
    case DISPUTE_CREATED = 'dispute_created';
    case DISPUTE_RESOLVED = 'dispute_resolved';
    
    // Offers
    case OFFER_EXPIRING = 'offer_expiring';
    case OFFER_SOLD_OUT = 'offer_sold_out';
    case LOW_STOCK = 'low_stock';
    
    // System
    case MAINTENANCE = 'maintenance';
    case PROMOTION = 'promotion';

    public function label(): string
    {
        return match($this) {
            self::WELCOME => 'Bienvenue',
            self::PHONE_VERIFIED => 'Téléphone vérifié',
            self::ACCOUNT_APPROVED => 'Compte approuvé',
            self::ORDER_CREATED => 'Nouvelle commande',
            self::ORDER_CONFIRMED => 'Commande confirmée',
            self::ORDER_CANCELLED => 'Commande annulée',
            self::ORDER_COMPLETED => 'Commande terminée',
            self::ITEM_CONFIRMED => 'Article confirmé',
            self::ITEM_CANCELLED => 'Article annulé',
            self::ITEM_READY => 'Article prêt',
            self::PAYMENT_RECEIVED => 'Paiement reçu',
            self::PAYMENT_RELEASED => 'Paiement libéré',
            self::PAYMENT_FAILED => 'Paiement échoué',
            self::DELIVERY_ASSIGNED => 'Livraison assignée',
            self::DELIVERY_STARTED => 'Livraison démarrée',
            self::DELIVERY_ARRIVED => 'Livraison arrivée',
            self::DELIVERY_COMPLETED => 'Livraison terminée',
            self::DELIVERY_DELAYED => 'Livraison retardée',
            self::NEW_RATING => 'Nouvelle évaluation',
            self::DISPUTE_CREATED => 'Litige créé',
            self::DISPUTE_RESOLVED => 'Litige résolu',
            self::OFFER_EXPIRING => 'Offre expirant',
            self::OFFER_SOLD_OUT => 'Offre épuisée',
            self::LOW_STOCK => 'Stock faible',
            self::MAINTENANCE => 'Maintenance',
            self::PROMOTION => 'Promotion',
        };
    }

    public function priority(): string
    {
        return match($this) {
            self::PAYMENT_FAILED,
            self::DELIVERY_DELAYED,
            self::DISPUTE_CREATED => 'high',
            
            self::ORDER_CREATED,
            self::PAYMENT_RECEIVED,
            self::DELIVERY_ASSIGNED => 'medium',
            
            default => 'low',
        };
    }

    public function shouldSendSMS(): bool
    {
        return in_array($this, [
            self::ORDER_CONFIRMED,
            self::PAYMENT_RECEIVED,
            self::DELIVERY_STARTED,
            self::DELIVERY_ARRIVED,
            self::PAYMENT_FAILED,
            self::DISPUTE_CREATED,
        ]);
    }

    public function shouldSendPush(): bool
    {
        // Toutes les notifications importantes
        return $this->priority() !== 'low';
    }
}
