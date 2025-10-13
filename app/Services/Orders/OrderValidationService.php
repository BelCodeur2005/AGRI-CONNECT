<?php

// app/Services/Orders/OrderValidationService.php
namespace App\Services\Orders;

use App\Models\Order;
use App\Models\Offer;

class OrderValidationService
{
    /**
     * Valider qu'une commande peut être créée
     */
    public function validateOrderCreation(array $items): array
    {
        $errors = [];

        foreach ($items as $index => $item) {
            $offer = Offer::find($item['offer_id']);

            if (!$offer) {
                $errors[] = "Item {$index}: Offre introuvable";
                continue;
            }

            if (!$offer->canOrder($item['quantity'])) {
                $errors[] = "Item {$index}: Stock insuffisant pour {$offer->product->name}";
            }
        }

        return $errors;
    }

    /**
     * Valider qu'un item peut être confirmé
     */
    public function validateItemConfirmation(OrderItem $item): bool
    {
        return $item->status->canBeConfirmed();
    }
}