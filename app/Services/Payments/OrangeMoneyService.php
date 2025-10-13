<?php
// app/Services/Payments/OrangeMoneyService.php
namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrangeMoneyService
{
    private string $apiUrl;
    private string $clientId;
    private string $clientSecret;
    private ?string $accessToken = null;

    public function __construct()
    {
        $this->apiUrl = config('services.orange_money.api_url');
        $this->clientId = config('services.orange_money.client_id');
        $this->clientSecret = config('services.orange_money.client_secret');
    }

    /**
     * Initier paiement Orange Money
     */
    public function initiate(Payment $payment): void
    {
        try {
            // 1. Obtenir token d'accès
            $this->getAccessToken();

            // 2. Initier paiement
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'X-Callback-Url' => route('api.webhooks.orange'),
            ])->post($this->apiUrl . '/omcoreapis/1.0.2/mp/pay', [
                'subscriber_msisdn' => $this->formatPhone($payment->payer_phone),
                'merchant_msisdn' => config('services.orange_money.merchant_phone'),
                'amount' => $payment->amount,
                'currency' => 'XAF',
                'order_id' => $payment->transaction_id,
                'reference' => $payment->order->order_number,
                'description' => "Commande Agri-Connect {$payment->order->order_number}",
            ]);

            if ($response->successful()) {
                $payment->update([
                    'payment_metadata' => $response->json(),
                    'operator_reference' => $response->json()['txnid'] ?? null,
                ]);
            } else {
                throw new \Exception('Échec initiation: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('Orange Money Error', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Vérifier statut paiement
     */
    public function checkStatus(Payment $payment): array
    {
        try {
            $this->getAccessToken();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->get($this->apiUrl . '/omcoreapis/1.0.2/mp/paymentstatus', [
                'order_id' => $payment->transaction_id,
            ]);

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Orange Money Status Check Error', ['error' => $e->getMessage()]);
            return ['status' => 'unknown'];
        }
    }

    /**
     * Rembourser
     */
    public function refund(Payment $payment): void
    {
        // TODO: Implémenter API de remboursement Orange Money
        Log::info('Orange Money Refund requested', ['payment_id' => $payment->id]);
    }

    /**
     * Gérer webhook Orange Money
     */
    public function handleWebhook(array $data): void
    {
        // Trouver paiement
        $payment = Payment::where('transaction_id', $data['order_id'] ?? null)->first();

        if (!$payment) {
            Log::warning('Payment not found for webhook', ['data' => $data]);
            return;
        }

        // Mettre à jour selon statut
        if (($data['status'] ?? '') === 'SUCCESS') {
            $payment->update(['status' => 'held', 'paid_at' => now()]);
            app(PaymentService::class)->confirmPayment($payment);
        } elseif (($data['status'] ?? '') === 'FAILED') {
            $payment->markAsFailed();
        }
    }

    /**
     * Obtenir token d'accès
     */
    private function getAccessToken(): void
    {
        if ($this->accessToken) {
            return;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode("{$this->clientId}:{$this->clientSecret}"),
        ])->post($this->apiUrl . '/oauth/v3/token', [
            'grant_type' => 'client_credentials',
        ]);

        if ($response->successful()) {
            $this->accessToken = $response->json()['access_token'];
        } else {
            throw new \Exception('Failed to get Orange Money access token');
        }
    }

    /**
     * Formater numéro téléphone
     */
    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (!str_starts_with($phone, '237')) {
            $phone = '237' . $phone;
        }

        return $phone;
    }
}