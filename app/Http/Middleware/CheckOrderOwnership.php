<?php

// app/Http/Middleware/CheckOrderOwnership.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Order;

class CheckOrderOwnership
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $relation = 'buyer'): Response
    {
        $user = $request->user();
        $order = $request->route('order');

        if (!$order instanceof Order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande introuvable',
            ], 404);
        }

        $hasAccess = match($relation) {
            'buyer' => $user->isBuyer() && $order->buyer_id === $user->buyer->id,
            'producer' => $user->isProducer() && $order->items()->where('producer_id', $user->producer->id)->exists(),
            'transporter' => $user->isTransporter() && $order->delivery && $order->delivery->transporter_id === $user->transporter->id,
            'any' => $this->checkAnyRelation($user, $order),
            default => false,
        };

        if (!$hasAccess && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'avez pas accès à cette commande',
            ], 403);
        }

        return $next($request);
    }

    /**
     * Vérifier si l'utilisateur a n'importe quelle relation avec la commande
     */
    private function checkAnyRelation($user, Order $order): bool
    {
        if ($user->isBuyer() && $order->buyer_id === $user->buyer->id) {
            return true;
        }

        if ($user->isProducer() && $order->items()->where('producer_id', $user->producer->id)->exists()) {
            return true;
        }

        if ($user->isTransporter() && $order->delivery && $order->delivery->transporter_id === $user->transporter->id) {
            return true;
        }

        return false;
    }
}