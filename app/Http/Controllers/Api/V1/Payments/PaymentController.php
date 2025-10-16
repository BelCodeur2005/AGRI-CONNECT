<?php

// app/Http/Controllers/Api/V1/Payments/PaymentController.php
namespace App\Http\Controllers\Api\V1\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\InitiatePaymentRequest;
use App\Http\Requests\Payments\ReleasePaymentRequest;
use App\Http\Resources\Payments\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * Initier paiement
     */
    public function initiate(InitiatePaymentRequest $request, Order $order): JsonResponse
    {
        $this->authorize('initiate', [Payment::class, $order]);

        $payment = $this->paymentService->initiate(
            $order,
            $request->payment_method,
            $request->phone
        );

        return response()->json([
            'success' => true,
            'message' => 'Paiement initié',
            'data' => new PaymentResource($payment),
        ]);
    }

    /**
     * Vérifier statut paiement
     */
    public function status(Payment $payment): JsonResponse
    {
        $status = $this->paymentService->checkStatus($payment);

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    /**
     * Libérer paiement (après livraison confirmée)
     */
    public function release(ReleasePaymentRequest $request, Order $order): JsonResponse
    {
        $payment = $order->payment;

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun paiement trouvé',
            ], 404);
        }

        $this->paymentService->releaseToProducers($payment);

        return response()->json([
            'success' => true,
            'message' => 'Paiement libéré aux producteurs',
        ]);
    }
}