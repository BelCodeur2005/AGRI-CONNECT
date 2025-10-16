<?php

// app/Http/Controllers/Api/V1/Deliveries/DeliveryController.php
namespace App\Http\Controllers\Api\V1\Deliveries;

use App\Http\Controllers\Controller;
use App\Http\Requests\Deliveries\UpdateLocationRequest;
use App\Http\Requests\Deliveries\CompleteDeliveryRequest;
use App\Http\Resources\Deliveries\DeliveryResource;
use App\Models\Delivery;
use App\Services\Logistics\DeliveryService;
use Illuminate\Http\JsonResponse;

class DeliveryController extends Controller
{
    public function __construct(
        private DeliveryService $deliveryService
    ) {}

    /**
     * Détail livraison
     */
    public function show(Delivery $delivery): JsonResponse
    {
        $this->authorize('view', $delivery);

        return response()->json([
            'success' => true,
            'data' => new DeliveryResource($delivery->load([
                'order',
                'transporter.user',
                'pickupLocation',
                'deliveryLocation',
            ])),
        ]);
    }

    /**
     * Démarrer livraison
     */
    public function start(Delivery $delivery): JsonResponse
    {
        $this->authorize('start', $delivery);

        $this->deliveryService->start($delivery);

        return response()->json([
            'success' => true,
            'message' => 'Livraison démarrée',
        ]);
    }

    /**
     * Mettre à jour localisation
     */
    public function updateLocation(UpdateLocationRequest $request, Delivery $delivery): JsonResponse
    {
        $this->authorize('update', $delivery);

        $delivery->updateLocation($request->latitude, $request->longitude);

        return response()->json([
            'success' => true,
            'message' => 'Localisation mise à jour',
        ]);
    }

    /**
     * Terminer livraison
     */
    public function complete(CompleteDeliveryRequest $request, Delivery $delivery): JsonResponse
    {
        $this->authorize('complete', $delivery);

        $this->deliveryService->complete($delivery, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Livraison terminée',
        ]);
    }
}
