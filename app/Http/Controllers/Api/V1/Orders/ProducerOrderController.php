<?php

// app/Http/Controllers/Api/V1/Orders/ProducerOrderController.php
namespace App\Http\Controllers\Api\V1\Orders;

use App\Http\Controllers\Controller;
use App\Http\Resources\Orders\ProducerOrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProducerOrderController extends Controller
{
    /**
     * Commandes du producteur
     */
    public function index(Request $request): JsonResponse
    {
        $producer = $request->user()->producer;

        $orders = Order::forProducer($producer->id)
            ->with(['buyer.user', 'items' => function($q) use ($producer) {
                $q->where('producer_id', $producer->id);
            }, 'delivery'])
            ->when($request->status, function($q) use ($request) {
                $q->whereHas('items', fn($subQ) => $subQ->where('status', $request->status));
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => ProducerOrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Commandes en attente de confirmation
     */
    public function pending(Request $request): JsonResponse
    {
        $producer = $request->user()->producer;

        $items = $producer->orderItems()
            ->pending()
            ->with('order.buyer.user', 'offer.product')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }
}

