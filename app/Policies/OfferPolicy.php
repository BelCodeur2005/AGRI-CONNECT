<?php

// app/Policies/OfferPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Offer;

class OfferPolicy
{
    /**
     * Voir la liste des offres (tous les utilisateurs)
     */
    public function viewAny(User $user): bool
    {
        return $user->isProducer();
    }

    /**
     * Voir une offre spécifique
     */
    public function view(?User $user, Offer $offer): bool
    {
        // Offres publiques visibles par tous (même non connectés)
        if ($offer->status->value === 'active') {
            return true;
        }

        // Offres inactives : seulement le propriétaire
        return $user && $user->isProducer() && $offer->producer_id === $user->producer->id;
    }

    /**
     * Créer une offre
     */
    public function create(User $user): bool
    {
        return $user->isProducer() && $user->producer->canCreateOffers();
    }

    /**
     * Modifier une offre
     */
    public function update(User $user, Offer $offer): bool
    {
        return $user->isProducer() && $offer->producer_id === $user->producer->id;
    }

    /**
     * Supprimer une offre
     */
    public function delete(User $user, Offer $offer): bool
    {
        return $user->isProducer() 
            && $offer->producer_id === $user->producer->id
            && !$offer->orderItems()->active()->exists(); // Pas de commandes actives
    }

    /**
     * Restaurer une offre (soft delete)
     */
    public function restore(User $user, Offer $offer): bool
    {
        return $user->isProducer() && $offer->producer_id === $user->producer->id;
    }

    /**
     * Ajuster le stock
     */
    public function adjustStock(User $user, Offer $offer): bool
    {
        return $user->isProducer() && $offer->producer_id === $user->producer->id;
    }
}