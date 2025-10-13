<?php

// app/Services/Orders/OrderItemService.php
namespace App\Services\Orders;

use App\Models\OrderItem;
use App\Models\Producer;

class OrderItemService
{
    /**
     * Confirmer un item
     */
    public function confirm(OrderItem $item, Producer $producer, ?string $notes = null): void
    {
        if ($item->producer_id !== $producer->id) {
            throw new \Exception('Non autorisé');
        }

        if ($notes) {
            $item->update(['producer_notes' => $notes]);
        }

        $item->confirm();

        // Événement
        // event(new \App\Events\Orders\OrderItemConfirmed($item));
    }

    /**
     * Refuser un item
     */
    public function reject(OrderItem $item, Producer $producer, string $reason): void
    {
        if ($item->producer_id !== $producer->id) {
            throw new \Exception('Non autorisé');
        }

        $item->cancel($reason);

        // Si tous items annulés, annuler commande
        if ($item->order->items_cancelled === $item->order->total_items) {
            $item->order->cancel('Tous les articles ont été annulés par les producteurs');
        }
    }

    /**
     * Marquer comme prêt
     */
    public function markReady(OrderItem $item, Producer $producer): void
    {
        if ($item->producer_id !== $producer->id) {
            throw new \Exception('Non autorisé');
        }

        $item->markAsReady();

        // Si tous items prêts, mettre commande en "ready_for_pickup"
        $allReady = $item->order->items()
            ->whereNotIn('status', ['cancelled'])
            ->where('status', '!=', 'ready')
            ->count() === 0;

        if ($allReady) {
            $item->order->update(['status' => 'ready_for_pickup']);
        }
    }

    /**
     * Obtenir items pour un producteur
     */
    public function getForProducer(Producer $producer, array $filters = [])
    {
        $query = OrderItem::where('producer_id', $producer->id)
            ->with(['order.buyer.user', 'offer.product']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['order_id'])) {
            $query->where('order_id', $filters['order_id']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }
}