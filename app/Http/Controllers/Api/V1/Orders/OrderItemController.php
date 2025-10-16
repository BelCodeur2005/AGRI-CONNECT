<?php

// app/Http/Controllers/Api/V1/Orders/OrderItemController.php
namespace App\Http\Controllers\Api\V1\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\ConfirmOrderItemRequest;
use App\Http\Requests\Orders\CancelOrderItemRequest;
use App\Models\OrderItem;
use App\Services\Orders\OrderItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    public function __construct(
        private OrderItemService $orderItemService
    ) {}

    /**
     * Confirmer item (producteur)
     */
    public function confirm(ConfirmOrderItemRequest $request, OrderItem $item): JsonResponse
    {
        $this->authorize('confirm', $item);

        $producer = $request->user()->producer;
        $this->orderItemService->confirm($item, $producer, $request->producer_notes);

        return response()->json([
            'success' => true,
            'message' => 'Article confirmé',
        ]);
    }

    /**
     * Refuser item (producteur)
     */
    public function reject(CancelOrderItemRequest $request, OrderItem $item): JsonResponse
    {
        $this->authorize('cancel', $item);

        $producer = $request->user()->producer;
        $this->orderItemService->reject($item, $producer, $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Article refusé',
        ]);
    }

    /**
     * Marquer comme prêt (producteur)
     */
    public function markReady(Request $request, OrderItem $item): JsonResponse
    {
        $this->authorize('update', $item);

        $producer = $request->user()->producer;
        $this->orderItemService->markReady($item, $producer);

        return response()->json([
            'success' => true,
            'message' => 'Article marqué comme prêt',
        ]);
    }
}