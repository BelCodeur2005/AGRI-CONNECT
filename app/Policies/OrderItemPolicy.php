<?php

// app/Policies/OrderItemPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\OrderItem;

class OrderItemPolicy
{
    /**
     * Voir un item de commande
     */
    public function view(User $user, OrderItem $item): bool
    {
        // Acheteur propriétaire de la commande
        if ($user->isBuyer() && $item->order->buyer_id === $user->buyer->id) {
            return true;
        }

        // Producteur de l'item
        if ($user->isProducer() && $item->producer_id === $user->producer->id) {
            return true;
        }

        // Admin
        return $user->isAdmin();
    }

    /**
     * Confirmer un item (producteur)
     */
    public function confirm(User $user, OrderItem $item): bool
    {
        return $user->isProducer() 
            && $item->producer_id === $user->producer->id
            && $item->status->canBeConfirmed();
    }

    /**
     * Annuler un item (producteur)
     */
    public function cancel(User $user, OrderItem $item): bool
    {
        return $user->isProducer() 
            && $item->producer_id === $user->producer->id
            && $item->status->canBeCancelled();
    }

    /**
     * Mettre à jour un item
     */
    public function update(User $user, OrderItem $item): bool
    {
        return $user->isProducer() 
            && $item->producer_id === $user->producer->id
            && $item->status->isActive();
    }

    /**
     * Marquer comme prêt
     */
    public function markReady(User $user, OrderItem $item): bool
    {
        return $user->isProducer() 
            && $item->producer_id === $user->producer->id
            && $item->status->value === 'confirmed';
    }
}