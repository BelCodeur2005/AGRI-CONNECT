<?php

// app/Services/Payments/PaymentService.php
namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\Payments\PaymentFailedException;
use App\Exceptions\Payments\PaymentAlreadyProcessedException;

class PaymentService
{
    public function __construct(
        private OrangeMoneyService $orangeMoneyService,
        private MtnMomoService $mtnMomoService,
        private EscrowService $escrowService,
        private PaymentSplitService $splitService
    ) {}

    /**
     * Initier un paiement
     */
    public function initiate(Order $order, string $method, string $phone): Payment
    {
        // Vérifier pas déjà payé
        if ($order->payment && $order->payment->status !== PaymentStatus::FAILED) {
            throw new PaymentAlreadyProcessedException($order->payment->transaction_id);
        }

        // Créer paiement
        $payment = Payment::create([
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'method' => $method,
            'status' => PaymentStatus::PENDING,
            'payer_id' => $order->buyer->user_id,
            'payer_phone' => $phone,
        ]);

        try {
            // Initier selon la méthode
            match($method) {
                'orange_money' => $this->orangeMoneyService->initiate($payment),
                'mtn_momo' => $this->mtnMomoService->initiate($payment),
                'cash' => $this->initiateCash($payment),
                default => throw new \Exception('Méthode non supportée'),
            };

            return $payment->fresh();

        } catch (\Exception $e) {
            $payment->markAsFailed();
            throw new PaymentFailedException($e->getMessage());
        }
    }

    /**
     * Paiement cash (phase pilote)
     */
    private function initiateCash(Payment $payment): void
    {
        // En phase pilote, on marque comme "held" pour validation manuelle
        $payment->update([
            'status' => PaymentStatus::HELD,
            'held_at' => now(),
            'payment_metadata' => ['note' => 'Paiement cash à la livraison'],
        ]);

        $payment->order->markAsPaid();
    }

    /**
     * Confirmer réception du paiement (webhook ou manuel)
     */
    public function confirmPayment(Payment $payment): void
    {
        if ($payment->status !== PaymentStatus::PENDING) {
            return;
        }

        // Bloquer le paiement en escrow
        $this->escrowService->hold($payment);

        // Créer les splits par producteur
        $this->splitService->createSplits($payment);

        // Mettre à jour commande
        $payment->order->markAsPaid();

        // Événement
        // event(new \App\Events\Payments\PaymentReceived($payment));
    }

    /**
     * Libérer paiement aux producteurs
     */
    public function releaseToProducers(Payment $payment): void
    {
        if (!$payment->canBeReleased()) {
            throw new \Exception('Ce paiement ne peut pas être libéré');
        }

        $this->escrowService->release($payment);

        // Événement
        // event(new \App\Events\Payments\PaymentReleased($payment));
    }

    /**
     * Rembourser acheteur
     */
    public function refund(Payment $payment, string $reason): void
    {
        if ($payment->status !== PaymentStatus::HELD) {
            throw new \Exception('Ce paiement ne peut pas être remboursé');
        }

        // Appeler API de remboursement selon méthode
        match($payment->method) {
            PaymentMethod::ORANGE_MONEY => $this->orangeMoneyService->refund($payment),
            PaymentMethod::MTN_MOMO => $this->mtnMomoService->refund($payment),
            PaymentMethod::CASH => null, // Géré manuellement
            default => null,
        };

        $payment->refund();
        $payment->order->update(['status' => 'refunded']);
    }

    /**
     * Vérifier statut paiement
     */
    public function checkStatus(Payment $payment): array
    {
        return match($payment->method) {
            PaymentMethod::ORANGE_MONEY => $this->orangeMoneyService->checkStatus($payment),
            PaymentMethod::MTN_MOMO => $this->mtnMomoService->checkStatus($payment),
            default => ['status' => $payment->status->value],
        };
    }

    /**
     * Gérer webhook
     */
    public function handleWebhook(array $data, string $provider): void
    {
        match($provider) {
            'orange' => $this->orangeMoneyService->handleWebhook($data),
            'mtn' => $this->mtnMomoService->handleWebhook($data),
            default => null,
        };
    }
}