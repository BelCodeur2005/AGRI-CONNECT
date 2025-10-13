<?php

// app/Services/Payments/MtnMomoService.php
namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MtnMomoService
{
    private string $apiUrl;
    private string $subscriptionKey;

    public function __construct()
    {
        $this->apiUrl = config('services.mtn_momo.api_url');
        $this->subscriptionKey = config('services.mtn_momo.subscription_key');
    }

    /**
     * Initier paiement MTN MoMo
     */
    public function initiate(Payment $payment): void
    {
        try {
            $referenceId = (string) Str::uuid();

            $response = Http::withHeaders([
                'X-Reference-Id' => $referenceId,
                'X-Target-Environment' => config('services.mtn_momo.environment', 'sandbox'),
                'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
            ])->post($this->apiUrl . '/collection/v1_0/requesttopay', [
                'amount' => (string) $payment->amount,
                'currency' => 'XAF',
                'externalId' => $payment->transaction_id,
                'payer' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => $this->formatPhone($payment->payer_phone),
                ],
                'payerMessage' => 'Paiement Agri-Connect',
                'payeeNote' => "Commande {$payment->order->order_number}",
            ]);

            if ($response->successful()) {
                $payment->update([
                    'payment_metadata' => ['reference_id' => $referenceId],
                    'operator_reference' => $referenceId,
                ]);
            } else {
                throw new \Exception('Échec initiation MTN MoMo');
            }

        } catch (\Exception $e) {
            Log::error('MTN MoMo Error', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Vérifier statut
     */
    public function checkStatus(Payment $payment): array
    {
        $referenceId = $payment->operator_reference;

        if (!$referenceId) {
            return ['status' => 'unknown'];
        }

        try {
            $response = Http::withHeaders([
                'X-Target-Environment' => config('services.mtn_momo.environment'),
                'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
            ])->get($this->apiUrl . "/collection/v1_0/requesttopay/{$referenceId}");

            return $response->json();

        } catch (\Exception $e) {
            Log::error('MTN MoMo Status Check Error', ['error' => $e->getMessage()]);
            return ['status' => 'unknown'];
        }
    }

    /**
     * Rembourser
     */
    public function refund(Payment $payment): void
    {
        // TODO: Implémenter API de remboursement MTN
        Log::info('MTN MoMo Refund requested', ['payment_id' => $payment->id]);
    }

    /**
     * Gérer webhook
     */
    public function handleWebhook(array $data): void
    {
        // Similaire à Orange Money
        Log::info('MTN MoMo Webhook received', ['data' => $data]);
    }

    /**
     * Obtenir access token
     */
    private function getAccessToken(): string
    {
        // TODO: Implémenter récupération token MTN
        return 'sandbox_token';
    }

    /**
     * Formater téléphone
     */
    private function formatPhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }
}