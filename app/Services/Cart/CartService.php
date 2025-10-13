<?php

// app/Services/Cart/CartService.php
namespace App\Services\Cart;

use App\Models\User;
use App\Models\CartItem;
use App\Models\Offer;
use App\Exceptions\Orders\EmptyCartException;
use App\Exceptions\Orders\InsufficientStockException;
use App\Exceptions\Catalog\OfferExpiredException;

class CartService
{
    /**
     * Ajouter au panier
     */
    public function addItem(User $user, int $offerId, float $quantity, ?string $notes = null): CartItem
    {
        $offer = Offer::findOrFail($offerId);

        // Vérifier disponibilité
        if (!$offer->canOrder($quantity)) {
            throw new InsufficientStockException(
                $offerId,
                $quantity,
                $offer->remaining_quantity
            );
        }

        // Vérifier si déjà dans le panier
        $existingItem = CartItem::where('user_id', $user->id)
            ->where('offer_id', $offerId)
            ->first();

        if ($existingItem) {
            return $this->updateItem($user, $existingItem->id, $quantity, $notes);
        }

        return CartItem::create([
            'user_id' => $user->id,
            'offer_id' => $offerId,
            'quantity' => $quantity,
            'notes' => $notes,
        ]);
    }

    /**
     * Mettre à jour item du panier
     */
    public function updateItem(User $user, int $cartItemId, float $quantity, ?string $notes = null): CartItem
    {
        $cartItem = CartItem::where('user_id', $user->id)
            ->findOrFail($cartItemId);

        if ($quantity <= 0) {
            $cartItem->delete();
            throw new \Exception('Article retiré du panier');
        }

        $cartItem->updateQuantity($quantity);

        if ($notes !== null) {
            $cartItem->update(['notes' => $notes]);
        }

        return $cartItem->fresh();
    }

    /**
     * Retirer du panier
     */
    public function removeItem(User $user, int $cartItemId): void
    {
        CartItem::where('user_id', $user->id)
            ->where('id', $cartItemId)
            ->delete();
    }

    /**
     * Vider le panier
     */
    public function clear(User $user): void
    {
        CartItem::where('user_id', $user->id)->delete();
    }

    /**
     * Obtenir le panier
     */
    public function getCart(User $user): array
    {
        $items = CartItem::where('user_id', $user->id)
            ->with(['offer.product', 'offer.producer.user', 'offer.location'])
            ->get();

        $validItems = $items->filter(fn($item) => $item->is_valid);
        $invalidItems = $items->filter(fn($item) => !$item->is_valid);

        return [
            'valid_items' => $validItems,
            'invalid_items' => $invalidItems,
            'summary' => $this->calculateSummary($validItems),
        ];
    }

    /**
     * Calculer résumé du panier
     */
    private function calculateSummary($items): array
    {
        $subtotal = $items->sum('subtotal');
        $totalItems = $items->count();
        $uniqueProducers = $items->pluck('offer.producer_id')->unique()->count();

        // Estimer frais de livraison
        $estimatedDeliveryCost = $this->estimateDeliveryCost($uniqueProducers);

        return [
            'subtotal' => $subtotal,
            'estimated_delivery_cost' => $estimatedDeliveryCost,
            'estimated_total' => $subtotal + $estimatedDeliveryCost,
            'total_items' => $totalItems,
            'unique_producers' => $uniqueProducers,
            'is_multi_producer' => $uniqueProducers > 1,
        ];
    }

    /**
     * Estimer coût de livraison
     */
    private function estimateDeliveryCost(int $producerCount): float
    {
        $baseCost = 5000; // 5000 FCFA de base
        $additionalCost = ($producerCount - 1) * 2000; // 2000 FCFA par producteur supplémentaire

        return $baseCost + max(0, $additionalCost);
    }

    /**
     * Valider panier avant checkout
     */
    public function validate(User $user): array
    {
        $items = CartItem::where('user_id', $user->id)->get();

        if ($items->isEmpty()) {
            throw new EmptyCartException();
        }

        $errors = [];

        foreach ($items as $item) {
            try {
                $item->validateBeforeCheckout();
            } catch (\Exception $e) {
                $errors[] = [
                    'item_id' => $item->id,
                    'offer_id' => $item->offer_id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Nettoyer items invalides
     */
    public function cleanInvalidItems(User $user): int
    {
        $items = CartItem::where('user_id', $user->id)->get();
        $deleted = 0;

        foreach ($items as $item) {
            if (!$item->is_valid) {
                $item->delete();
                $deleted++;
            }
        }

        return $deleted;
    }
}