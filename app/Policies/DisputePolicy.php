<?php

// app/Policies/DisputePolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Dispute;
use App\Models\Order;

class DisputePolicy
{
    /**
     * Voir la liste des litiges
     */
    public function viewAny(User $user): bool
    {
        return true; // Tous les utilisateurs connectés
    }

    /**
     * Voir un litige spécifique
     */
    public function view(User $user, Dispute $dispute): bool
    {
        // Parties impliquées dans le litige
        if ($user->id === $dispute->reported_by || $user->id === $dispute->reported_against) {
            return true;
        }

        // Producteur/acheteur/transporteur de la commande
        $order = $dispute->order;
        
        if ($user->isBuyer() && $order->buyer_id === $user->buyer->id) {
            return true;
        }

        if ($user->isProducer()) {
            return $order->items()->where('producer_id', $user->producer->id)->exists();
        }

        if ($user->isTransporter() && $order->delivery) {
            return $order->delivery->transporter_id === $user->transporter->id;
        }

        // Admin
        return $user->isAdmin();
    }

    /**
     * Créer un litige
     */
    public function create(User $user, Order $order): bool
    {
        // Vérifier qu'il n'y a pas déjà un litige ouvert
        $existingDispute = Dispute::where('order_id', $order->id)
            ->where('reported_by', $user->id)
            ->where('status', '!=', \App\Enums\DisputeStatus::CLOSED)
            ->exists();

        if ($existingDispute) {
            return false;
        }

        // Acheteur peut ouvrir litige après livraison (dans les 3 jours)
        if ($user->isBuyer() && $order->buyer_id === $user->buyer->id) {
            return $order->status->value === 'delivered'
                && $order->delivered_at
                && now()->diffInDays($order->delivered_at) <= 3;
        }

        // Producteur peut ouvrir litige
        if ($user->isProducer()) {
            return $order->items()->where('producer_id', $user->producer->id)->exists()
                && in_array($order->status->value, ['confirmed', 'paid', 'delivered', 'completed']);
        }

        // Transporteur peut ouvrir litige
        if ($user->isTransporter() && $order->delivery) {
            return $order->delivery->transporter_id === $user->transporter->id;
        }

        return false;
    }

    /**
     * Mettre à jour un litige
     */
    public function update(User $user, Dispute $dispute): bool
    {
        // Seul celui qui a ouvert le litige peut le mettre à jour
        return $user->id === $dispute->reported_by 
            && $dispute->status->isActive();
    }

    /**
     * Escalader un litige
     */
    public function escalate(User $user, Dispute $dispute): bool
    {
        // Parties impliquées peuvent escalader
        return ($user->id === $dispute->reported_by || $user->id === $dispute->reported_against)
            && $dispute->status->value === 'investigating';
    }

    /**
     * Résoudre un litige (admin seulement)
     */
    public function resolve(User $user, Dispute $dispute): bool
    {
        return $user->isAdmin() && $dispute->status->isActive();
    }

    /**
     * Fermer un litige
     */
    public function close(User $user, Dispute $dispute): bool
    {
        // Celui qui a ouvert peut fermer si résolu, ou admin
        return ($user->id === $dispute->reported_by && $dispute->status->value === 'resolved')
            || $user->isAdmin();
    }
}