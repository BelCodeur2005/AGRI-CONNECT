<?php

// app/Policies/PaymentPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Payment;
use App\Models\Order;

class PaymentPolicy
{
    /**
     * Voir un paiement
     */
    public function view(User $user, Payment $payment): bool
    {
        $order = $payment->order;

        // Acheteur propriétaire
        if ($user->isBuyer() && $order->buyer_id === $user->buyer->id) {
            return true;
        }

        // Producteur ayant un split dans ce paiement
        if ($user->isProducer()) {
            return $payment->splits()->where('producer_id', $user->producer->id)->exists();
        }

        // Admin
        return $user->isAdmin();
    }

    /**
     * Initier un paiement
     */
    public function initiate(User $user, Order $order): bool
    {
        return $user->isBuyer() 
            && $order->buyer_id === $user->buyer->id
            && $order->status->value === 'confirmed'
            && (!$order->payment || $order->payment->status->value === 'failed');
    }

    /**
     * Vérifier le statut d'un paiement
     */
    public function checkStatus(User $user, Payment $payment): bool
    {
        $order = $payment->order;

        return ($user->isBuyer() && $order->buyer_id === $user->buyer->id)
            || $user->isAdmin();
    }

    /**
     * Libérer un paiement
     */
    public function release(User $user, Payment $payment): bool
    {
        $order = $payment->order;

        // Acheteur peut libérer après livraison
        if ($user->isBuyer() && $order->buyer_id === $user->buyer->id) {
            return $order->status->value === 'delivered' 
                && $payment->status->value === 'held';
        }

        // Admin peut libérer
        return $user->isAdmin();
    }

    /**
     * Rembourser un paiement
     */
    public function refund(User $user, Payment $payment): bool
    {
        // Seul admin peut rembourser
        return $user->isAdmin() && $payment->status->value === 'held';
    }
}