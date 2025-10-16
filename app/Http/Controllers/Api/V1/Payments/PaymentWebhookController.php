<?php

// app/Http/Controllers/Api/V1/Payments/PaymentWebhookController.php
namespace App\Http\Controllers\Api\V1\Payments;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * Webhook Orange Money
     */
    public function orangeMoney(Request $request): Response
    {
        $this->paymentService->handleWebhook($request->all(), 'orange');
        return response()->noContent();
    }

    /**
     * Webhook MTN MoMo
     */
    public function mtnMomo(Request $request): Response
    {
        $this->paymentService->handleWebhook($request->all(), 'mtn');
        return response()->noContent();
    }
}
