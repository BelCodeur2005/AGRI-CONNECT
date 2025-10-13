<?php

// app/Services/Logistics/DeliveryGroupService.php
namespace App\Services\Logistics;

use App\Models\DeliveryGroup;
use App\Models\Delivery;
use App\Models\Transporter;
use Illuminate\Support\Collection;

class DeliveryGroupService
{
    /**
     * Créer groupe de livraison
     */
    public function create(Collection $deliveries, array $data): DeliveryGroup
    {
        $group = DeliveryGroup::create([
            'delivery_location_id' => $data['delivery_location_id'],
            'delivery_address' => $data['delivery_address'],
            'scheduled_date' => $data['scheduled_date'],
            'scheduled_time_from' => $data['scheduled_time_from'] ?? null,
            'scheduled_time_to' => $data['scheduled_time_to'] ?? null,
            'status' => 'pending',
        ]);

        // Ajouter les livraisons au groupe
        foreach ($deliveries as $index => $delivery) {
            $delivery->update([
                'delivery_group_id' => $group->id,
                'sequence_in_group' => $index + 1,
            ]);
        }

        $group->calculateTotals();

        return $group;
    }

    /**
     * Assigner transporteur au groupe
     */
    public function assignTransporter(DeliveryGroup $group, Transporter $transporter): void
    {
        $group->assignToTransporter($transporter);

        // Événement
        event(new \App\Events\Deliveries\DeliveryGroupAssigned($group));
    }

    /**
     * Démarrer groupe
     */
    public function start(DeliveryGroup $group): void
    {
        $group->update(['status' => 'in_progress']);

        // Démarrer première livraison
        $firstDelivery = $group->deliveries()->orderBy('sequence_in_group')->first();
        if ($firstDelivery) {
            $firstDelivery->markAsPickedUp();
        }
    }

    /**
     * Groupes disponibles pour un transporteur
     */
    public function getAvailableForTransporter(Transporter $transporter)
    {
        return DeliveryGroup::pending()
            ->whereHas('deliveryLocation', function($q) use ($transporter) {
                $q->whereIn('id', $transporter->service_areas ?? []);
            })
            ->with(['deliveries.order', 'deliveryLocation'])
            ->get();
    }
}