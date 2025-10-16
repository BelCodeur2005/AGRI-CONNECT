<?php

// app/Policies/OrderPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Order;

class OrderPolicy
{
    /**
     * Voir la liste des commandes
     */
    public function viewAny(User $user): bool
    {
        return $user->isBuyer() || $user->isProducer();
    }

    /**
     * Voir une commande spécifique
     */
    public function view(User $user, Order $order): bool
    {
        // Acheteur propriétaire
        if ($user->isBuyer() && $order->buyer_id === $user->buyer->id) {
            return true;
        }

        // Producteur ayant des items dans la commande
        if ($user->isProducer()) {
            return $order->items()->where('producer_id', $user->producer->id)->exists();
        }

        // Transporteur assigné
        if ($user->isTransporter() && $order->delivery) {
            return $order->delivery->transporter_id === $user->transporter->id;
        }

        // Admin
        return $user->isAdmin();
    }

    /**
     * Créer une commande
     */
    public function create(User $user): bool
    {
        return $user->isBuyer() && $user->hasCompleteProfile();
    }

    /**
     * Annuler une commande
     */
    public function cancel(User $user, Order $order): bool
    {
        // Acheteur peut annuler si statut le permet
        if ($user->isBuyer() && $order->buyer_id === $user->buyer->id) {
            return $order->canBeCancelled();
        }

        // Admin peut toujours annuler
        return $user->isAdmin();
    }

    /**
     * Confirmer réception (acheteur)
     */
    public function confirm(User $user, Order $order): bool
    {
        return $user->isBuyer() 
            && $order->buyer_id === $user->buyer->id
            && $order->status->value === 'delivered';
    }

    /**
     * Noter une commande
     */
    public function rate(User $user, Order $order): bool
    {
        // Acheteur peut noter
        if ($user->isBuyer() && $order->buyer_id === $user->buyer->id) {
            return $order->canBeRated();
        }

        // Producteur peut noter l'acheteur
        if ($user->isProducer()) {
            return $order->items()->where('producer_id', $user->producer->id)->exists()
                && $order->status->value === 'completed';
        }

        // Transporteur peut noter
        if ($user->isTransporter() && $order->delivery) {
            return $order->delivery->transporter_id === $user->transporter->id
                && $order->status->value === 'completed';
        }

        return false;
    }
}