<?php

// app/Http/Controllers/Api/V1/Deliveries/TransporterDeliveryController.php
namespace App\Http\Controllers\Api\V1\Deliveries;

use App\Http\Controllers\Controller;
use App\Http\Resources\Deliveries\DeliveryResource;
use App\Services\Logistics\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransporterDeliveryController extends Controller
{
    public function __construct(
        private DeliveryService $deliveryService
    ) {}

    /**
     * Livraisons du transporteur
     */
    public function index(Request $request): JsonResponse
    {
        $transporter = $request->user()->transporter;

        $deliveries = $transporter->deliveries()
            ->with('order.buyer.user')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => DeliveryResource::collection($deliveries),
        ]);
    }

    /**
     * Livraisons disponibles
     */
    public function available(Request $request): JsonResponse
    {
        $transporter = $request->user()->transporter;
        $available = $this->deliveryService->getAvailableForTransporter($transporter);

        return response()->json([
            'success' => true,
            'data' => DeliveryResource::collection($available),
        ]);
    }

    /**
     * Livraisons actives
     */
    public function active(Request $request): JsonResponse
    {
        $transporter = $request->user()->transporter;

        $active = $transporter->activeDeliveries()
            ->with('order.buyer.user')
            ->get();

        return response()->json([
            'success' => true,
            'data' => DeliveryResource::collection($active),
        ]);
    }
}
