<?php

// app/Policies/DeliveryPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Delivery;

class DeliveryPolicy
{
    /**
     * Voir une livraison
     */
    public function view(User $user, Delivery $delivery): bool
    {
        // Acheteur de la commande
        if ($user->isBuyer() && $delivery->order->buyer_id === $user->buyer->id) {
            return true;
        }

        // Producteur ayant des items dans la commande
        if ($user->isProducer()) {
            return $delivery->order->items()->where('producer_id', $user->producer->id)->exists();
        }

        // Transporteur assigné
        if ($user->isTransporter() && $delivery->transporter_id === $user->transporter->id) {
            return true;
        }

        // Admin
        return $user->isAdmin();
    }

    /**
     * Accepter une livraison (transporteur)
     */
    public function accept(User $user, Delivery $delivery): bool
    {
        return $user->isTransporter() 
            && $delivery->status->value === 'pending'
            && $user->transporter->is_available
            && $user->transporter->is_certified;
    }

    /**
     * Démarrer une livraison
     */
    public function start(User $user, Delivery $delivery): bool
    {
        return $user->isTransporter() 
            && $delivery->transporter_id === $user->transporter->id
            && $delivery->status->value === 'assigned';
    }

    /**
     * Mettre à jour une livraison
     */
    public function update(User $user, Delivery $delivery): bool
    {
        return $user->isTransporter() 
            && $delivery->transporter_id === $user->transporter->id
            && in_array($delivery->status->value, ['assigned', 'picked_up', 'in_transit']);
    }

    /**
     * Terminer une livraison
     */
    public function complete(User $user, Delivery $delivery): bool
    {
        return $user->isTransporter() 
            && $delivery->transporter_id === $user->transporter->id
            && in_array($delivery->status->value, ['in_transit', 'arrived']);
    }

    /**
     * Mettre à jour la localisation
     */
    public function updateLocation(User $user, Delivery $delivery): bool
    {
        return $user->isTransporter() 
            && $delivery->transporter_id === $user->transporter->id
            && in_array($delivery->status->value, ['picked_up', 'in_transit']);
    }

    /**
     * Signaler un problème
     */
    public function reportIssue(User $user, Delivery $delivery): bool
    {
        return $user->isTransporter() 
            && $delivery->transporter_id === $user->transporter->id;
    }
}