<?php

// app/Services/Orders/OrderService.php
namespace App\Services\Orders;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Services\Cart\CartService;
use App\Exceptions\Orders\EmptyCartException;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private CartService $cartService,
        private OrderCalculationService $calculationService,
        private OrderValidationService $validationService
    ) {}

    /**
     * Créer commande depuis le panier
     */
    public function createFromCart(User $user, array $data): Order
    {
        DB::beginTransaction();

        try {
            $buyer = $user->buyer;

            // Valider panier
            $cartValidation = $this->cartService->validate($user);
            if (!$cartValidation['valid']) {
                throw new \Exception('Panier invalide: ' . json_encode($cartValidation['errors']));
            }

            // Récupérer items du panier
            $cartItems = CartItem::where('user_id', $user->id)
                ->with('offer')
                ->get();

            if ($cartItems->isEmpty()) {
                throw new EmptyCartException();
            }

            // Créer commande
            $order = Order::create([
                'buyer_id' => $buyer->id,
                'delivery_address' => $data['delivery_address'],
                'delivery_location_id' => $data['delivery_location_id'],
                'requested_delivery_date' => $data['requested_delivery_date'] ?? now()->addDays(2),
                'delivery_notes' => $data['delivery_notes'] ?? null,
                'status' => 'pending',
            ]);

            // Créer order items
            foreach ($cartItems as $cartItem) {
                $this->createOrderItem($order, $cartItem);
            }

            // Calculer totaux
            $order->calculateTotals();

            // Calculer frais de livraison
            $deliveryCost = $this->calculationService->calculateDeliveryCost(
                $order,
                $cartItems->pluck('offer.location_id')->unique()->toArray(),
                $data['delivery_location_id']
            );

            $order->update(['delivery_cost' => $deliveryCost]);
            $order->calculateTotals();

            // Vider le panier
            $this->cartService->clear($user);

            DB::commit();

            return $order->fresh(['items.offer.product', 'items.producer.user']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Créer un order item depuis cart item
     */
    private function createOrderItem(Order $order, CartItem $cartItem): OrderItem
    {
        $offer = $cartItem->offer;
        $subtotal = $cartItem->subtotal;
        $commission = $this->calculationService->calculateCommission($subtotal);

        // Réserver stock
        $offer->reserveQuantity($cartItem->quantity);

        return OrderItem::create([
            'order_id' => $order->id,
            'offer_id' => $offer->id,
            'producer_id' => $offer->producer_id,
            'product_id' => $offer->product_id,
            'product_name' => $offer->product->name,
            'quantity' => $cartItem->quantity,
            'unit_price' => $offer->price_per_unit,
            'subtotal' => $subtotal,
            'platform_commission' => $commission,
            'status' => 'pending',
        ]);
    }

    /**
     * Confirmer une commande (acheteur)
     */
    public function confirmByBuyer(Order $order): void
    {
        if ($order->status !== \App\Enums\OrderStatus::DELIVERED) {
            throw new \Exception('La commande doit être livrée avant confirmation');
        }

        $order->complete();

        // Marquer tous les items comme completed
        $order->items->each->complete();

        // Événement pour libérer paiement
        // event(new \App\Events\Orders\OrderCompleted($order));
    }

    /**
     * Annuler une commande
     */
    public function cancel(Order $order, string $reason, User $user): void
    {
        if (!$order->canBeCancelled()) {
            throw new \Exception('Cette commande ne peut plus être annulée');
        }

        $order->cancel($reason);

        // Log activité
        $order->logActivity('cancelled', [
            'cancelled_by' => $user->id,
            'reason' => $reason,
        ]);

        // Événement
        // event(new \App\Events\Orders\OrderCancelled($order));
    }

    /**
     * Obtenir commandes d'un acheteur
     */
    public function getBuyerOrders(int $buyerId, array $filters = [])
    {
        $query = Order::where('buyer_id', $buyerId)
            ->with(['items.offer.product', 'items.producer.user', 'delivery', 'payment']);

        // Filtres
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Obtenir commandes d'un producteur
     */
    public function getProducerOrders(int $producerId, array $filters = [])
    {
        $query = Order::forProducer($producerId)
            ->with(['buyer.user', 'items' => function($q) use ($producerId) {
                $q->where('producer_id', $producerId);
            }, 'delivery']);

        // Filtres
        if (isset($filters['status'])) {
            $query->whereHas('items', function($q) use ($filters) {
                $q->where('status', $filters['status']);
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }
}
