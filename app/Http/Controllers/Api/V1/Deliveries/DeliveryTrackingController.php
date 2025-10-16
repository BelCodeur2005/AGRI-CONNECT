<?php

// app/Http/Controllers/Api/V1/Deliveries/DeliveryTrackingController.php
namespace App\Http\Controllers\Api\V1\Deliveries;

use App\Http\Controllers\Controller;
use App\Http\Resources\Deliveries\DeliveryTrackingResource;
use App\Models\Delivery;
use Illuminate\Http\JsonResponse;

class DeliveryTrackingController extends Controller
{
    /**
     * Suivi de livraison
     */
    public function show(Delivery $delivery): JsonResponse
    {
        // Accessible par acheteur ou transporteur
        $user = auth()->user();
        
        if (!$user->isBuyer() && !$user->isTransporter()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new DeliveryTrackingResource($delivery->load([
                'order',
                'transporter.user',
            ])),
        ]);
    }
}