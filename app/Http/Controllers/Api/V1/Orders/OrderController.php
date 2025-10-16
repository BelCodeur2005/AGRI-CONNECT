<?php

// app/Http/Controllers/Api/V1/Orders/OrderController.php
namespace App\Http\Controllers\Api\V1\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\CreateOrderRequest;
use App\Http\Requests\Orders\CancelOrderRequest;
use App\Http\Resources\Orders\OrderDetailResource;
use App\Models\Order;
use App\Services\Orders\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * Liste des commandes
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isBuyer()) {
            $orders = $this->orderService->getBuyerOrders(
                $user->buyer->id,
                $request->all()
            );
        } elseif ($user->isProducer()) {
            $orders = $this->orderService->getProducerOrders(
                $user->producer->id,
                $request->all()
            );
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non autorisé',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => OrderDetailResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Créer commande
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createFromCart(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Commande créée avec succès',
            'data' => new OrderDetailResource($order),
        ], 201);
    }

    /**
     * Détail commande
     */
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        return response()->json([
            'success' => true,
            'data' => new OrderDetailResource($order->load([
                'items.offer.product',
                'items.producer.user',
                'buyer.user',
                'payment',
                'delivery',
            ])),
        ]);
    }

    /**
     * Annuler commande
     */
    public function cancel(CancelOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('cancel', $order);

        $this->orderService->cancel($order, $request->reason, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Commande annulée',
        ]);
    }

    /**
     * Confirmer réception (acheteur)
     */
    public function confirm(Request $request, Order $order): JsonResponse
    {
        $this->authorize('confirm', $order);

        $this->orderService->confirmByBuyer($order);

        return response()->json([
            'success' => true,
            'message' => 'Commande confirmée',
        ]);
    }
}
