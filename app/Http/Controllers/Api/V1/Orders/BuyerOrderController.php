<?php

// app/Http/Controllers/Api/V1/Orders/BuyerOrderController.php
namespace App\Http\Controllers\Api\V1\Orders;

use App\Http\Controllers\Controller;
use App\Http\Resources\Orders\BuyerOrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuyerOrderController extends Controller
{
    /**
     * Commandes de l'acheteur
     */
    public function index(Request $request): JsonResponse
    {
        $buyer = $request->user()->buyer;

        $orders = Order::where('buyer_id', $buyer->id)
            ->with(['items.offer.product', 'items.producer.user', 'delivery', 'payment'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => BuyerOrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'total' => $orders->total(),
            ],
        ]);
    }
}
