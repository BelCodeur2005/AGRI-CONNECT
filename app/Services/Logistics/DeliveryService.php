<?php

// app/Services/Logistics/DeliveryService.php
namespace App\Services\Logistics;

use App\Models\Order;
use App\Models\Delivery;
use App\Models\Transporter;
use App\Exceptions\Logistics\NoTransporterAvailableException;

class DeliveryService
{
    public function __construct(
        private TransporterMatchingService $matchingService,
        private RouteOptimizationService $optimizationService
    ) {}

    /**
     * Créer livraison pour une commande
     */
    public function createForOrder(Order $order): Delivery
    {
        // Déterminer lieu de collecte (premier producteur si multi-producteurs)
        $firstItem = $order->items->first();
        $pickupLocation = $firstItem->offer->location;

        return Delivery::create([
            'order_id' => $order->id,
            'pickup_location_id' => $pickupLocation->id,
            'pickup_address' => $pickupLocation->name,
            'delivery_location_id' => $order->delivery_location_id,
            'delivery_address' => $order->delivery_address,
            'scheduled_pickup_at' => now()->addDay(),
            'scheduled_delivery_at' => now()->addDays(2),
            'status' => 'pending',
        ]);
    }

    /**
     * Assigner transporteur automatiquement
     */
    public function autoAssign(Delivery $delivery): ?Transporter
    {
        $weight = $delivery->order->items->sum('quantity');
        
        $transporter = $this->matchingService->findBest(
            $delivery->pickup_location_id,
            $delivery->delivery_location_id,
            $weight
        );

        if (!$transporter) {
            throw new NoTransporterAvailableException([
                'delivery_id' => $delivery->id,
                'weight' => $weight,
            ]);
        }

        $delivery->assignToTransporter($transporter);

        return $transporter;
    }

    /**
     * Démarrer livraison
     */
    public function start(Delivery $delivery): void
    {
        $delivery->markAsPickedUp();
        $delivery->startTransit();
        $delivery->order->update(['status' => 'in_transit']);

        // Événement
        event(new \App\Events\Deliveries\DeliveryStarted($delivery));
    }

    /**
     * Terminer livraison
     */
    public function complete(Delivery $delivery, array $data): void
    {
        $proofPhoto = null;
        
        if (isset($data['proof_photo'])) {
            $proofPhoto = $data['proof_photo']->store('delivery-proofs', 'public');
        }

        $delivery->complete($proofPhoto, $data['signature'] ?? null);
        $delivery->order->markAsDelivered();

        // Mettre à jour earnings transporteur
        $delivery->transporter->incrementEarnings($delivery->order->delivery_cost);

        // Événement
        event(new \App\Events\Deliveries\DeliveryCompleted($delivery));
    }

    /**
     * Obtenir livraisons disponibles pour un transporteur
     */
    public function getAvailableForTransporter(Transporter $transporter)
    {
        return Delivery::where('status', 'pending')
            ->whereHas('order', function($q) {
                $q->where('status', 'ready_for_pickup');
            })
            ->with(['order.items.offer.product', 'pickupLocation', 'deliveryLocation'])
            ->get()
            ->filter(function($delivery) use ($transporter) {
                // Vérifier zones de service
                return $transporter->serveLocation($delivery->pickup_location_id)
                    || $transporter->serveLocation($delivery->delivery_location_id);
            });
    }
}