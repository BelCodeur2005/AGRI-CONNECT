<?php

// app/Http/Controllers/Api/V1/Payments/PaymentHistoryController.php
namespace App\Http\Controllers\Api\V1\Payments;

use App\Http\Controllers\Controller;
use App\Http\Resources\Payments\PaymentResource;
use App\Http\Resources\Payments\PaymentSplitResource;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentHistoryController extends Controller
{
    /**
     * Historique paiements (acheteur)
     */
    public function buyer(Request $request): JsonResponse
    {
        $buyer = $request->user()->buyer;

        $payments = Payment::whereHas('order', function($q) use ($buyer) {
            $q->where('buyer_id', $buyer->id);
        })
        ->with('order')
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => PaymentResource::collection($payments),
        ]);
    }

    /**
     * Historique revenus (producteur)
     */
    public function producer(Request $request): JsonResponse
    {
        $producer = $request->user()->producer;

        $splits = $producer->paymentSplits()
            ->with('payment.order')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => PaymentSplitResource::collection($splits),
            'summary' => [
                'pending' => $producer->pending_earnings,
                'released' => $producer->total_earnings_released,
                'total' => $producer->total_revenue,
            ],
        ]);
    }
}