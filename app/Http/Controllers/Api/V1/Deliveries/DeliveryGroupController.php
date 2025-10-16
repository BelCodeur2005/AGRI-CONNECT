<?php

// app/Http/Controllers/Api/V1/Deliveries/DeliveryGroupController.php
namespace App\Http\Controllers\Api\V1\Deliveries;

use App\Http\Controllers\Controller;
use App\Http\Resources\Deliveries\DeliveryGroupResource;
use App\Models\DeliveryGroup;
use App\Services\Logistics\DeliveryGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryGroupController extends Controller
{
    public function __construct(
        private DeliveryGroupService $groupService
    ) {}

    /**
     * Groupes du transporteur
     */
    public function index(Request $request): JsonResponse
    {
        $transporter = $request->user()->transporter;

        $groups = DeliveryGroup::forTransporter($transporter->id)
            ->with('deliveries.order')
            ->orderBy('scheduled_date', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => DeliveryGroupResource::collection($groups),
        ]);
    }

    /**
     * Groupes disponibles
     */
    public function available(Request $request): JsonResponse
    {
        $transporter = $request->user()->transporter;
        $groups = $this->groupService->getAvailableForTransporter($transporter);

        return response()->json([
            'success' => true,
            'data' => DeliveryGroupResource::collection($groups),
        ]);
    }
}