<?php

// app/Http/Controllers/Api/V1/Cart/CartController.php
namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Requests\Cart\CheckoutCartRequest;
use App\Http\Resources\Cart\CartSummaryResource;
use App\Services\Cart\CartService;
use App\Services\Orders\OrderService;
use App\Models\CartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService
    ) {}

    /**
     * Obtenir panier
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart($request->user());

        return response()->json([
            'success' => true,
            'data' => new CartSummaryResource($cart),
        ]);
    }

    /**
     * Ajouter au panier
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        $cartItem = $this->cartService->addItem(
            $request->user(),
            $request->offer_id,
            $request->quantity,
            $request->notes
        );

        return response()->json([
            'success' => true,
            'message' => 'Article ajouté au panier',
            'data' => $cartItem->load('offer.product'),
        ], 201);
    }

    /**
     * Mettre à jour item
     */
    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        try {
            $cartItem = $this->cartService->updateItem(
                $request->user(),
                $cartItem->id,
                $request->quantity,
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Panier mis à jour',
                'data' => $cartItem->load('offer.product'),
            ]);

        } catch (\Exception $e) {
            if ($e->getMessage() === 'Article retiré du panier') {
                return response()->json([
                    'success' => true,
                    'message' => 'Article retiré du panier',
                ]);
            }
            throw $e;
        }
    }

    /**
     * Retirer du panier
     */
    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        $this->cartService->removeItem($request->user(), $cartItem->id);

        return response()->json([
            'success' => true,
            'message' => 'Article retiré du panier',
        ]);
    }

    /**
     * Vider panier
     */
    public function clear(Request $request): JsonResponse
    {
        $this->cartService->clear($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Panier vidé',
        ]);
    }

    /**
     * Valider panier
     */
    public function validate(Request $request): JsonResponse
    {
        $validation = $this->cartService->validate($request->user());

        return response()->json([
            'success' => $validation['valid'],
            'data' => $validation,
        ], $validation['valid'] ? 200 : 422);
    }

    /**
     * Commander (Checkout)
     */
    public function checkout(CheckoutCartRequest $request): JsonResponse
    {
        $order = $this->orderService->createFromCart(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Commande créée avec succès',
            'data' => $order,
        ], 201);
    }
}
