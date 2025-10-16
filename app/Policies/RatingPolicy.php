<?php

// app/Policies/RatingPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Rating;
use App\Models\Order;

class RatingPolicy
{
    /**
     * Voir la liste de toutes les évaluations
     */
    public function viewAny(User $user): bool
    {
        return true; // Tous les utilisateurs connectés peuvent voir les évaluations
    }

    /**
     * Voir une évaluation spécifique
     */
    public function view(?User $user, Rating $rating): bool
    {
        // Évaluations publiques visibles par tous
        return true;
    }

    /**
     * Créer une évaluation
     */
    public function create(User $user, Order $order): bool
    {
        // Ne peut pas noter si déjà noté
        if ($rating = Rating::where('order_id', $order->id)
            ->where('rater_id', $user->id)
            ->exists()) {
            return false;
        }

        // Acheteur peut noter après livraison
        if ($user->isBuyer() && $order->buyer_id === $user->buyer->id) {
            return in_array($order->status->value, ['delivered', 'completed']);
        }

        // Producteur peut noter acheteur après commande complétée
        if ($user->isProducer()) {
            return $order->items()->where('producer_id', $user->producer->id)->exists()
                && $order->status->value === 'completed';
        }

        // Transporteur peut noter après livraison
        if ($user->isTransporter() && $order->delivery) {
            return $order->delivery->transporter_id === $user->transporter->id
                && $order->delivery->status->value === 'delivered';
        }

        return false;
    }

    /**
     * Modifier une évaluation
     */
    public function update(User $user, Rating $rating): bool
    {
        // Peut modifier dans les 48h
        return $user->id === $rating->rater_id 
            && $rating->created_at->gt(now()->subHours(48));
    }

    /**
     * Supprimer une évaluation
     */
    public function delete(User $user, Rating $rating): bool
    {
        // Peut supprimer dans les 72h ou si admin
        return ($user->id === $rating->rater_id && $rating->created_at->gt(now()->subHours(72)))
            || $user->isAdmin();
    }

    /**
     * Répondre à une évaluation
     */
    public function respond(User $user, Rating $rating): bool
    {
        // La personne notée ou admin peuvent répondre
        return $rating->rateable->user_id === $user->id || $user->isAdmin();
    }
}