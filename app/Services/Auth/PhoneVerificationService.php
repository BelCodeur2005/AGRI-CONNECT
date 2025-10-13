<?php

// app/Services/Auth/PhoneVerificationService.php
namespace App\Services\Auth;

use App\Models\PhoneVerification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PhoneVerificationService
{
    /**
     * Générer code de vérification
     */
    public function generate(string $phone): PhoneVerification
    {
        // Invalider anciens codes
        PhoneVerification::where('phone', $phone)
            ->where('is_verified', false)
            ->update(['is_verified' => true]);

        // Générer nouveau code
        $code = $this->generateCode();

        return PhoneVerification::create([
            'phone' => $phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);
    }

    /**
     * Vérifier code
     */
    public function verify(string $phone, string $code): bool
    {
        $verification = PhoneVerification::where('phone', $phone)
            ->where('is_verified', false)
            ->latest()
            ->first();

        if (!$verification) {
            return false;
        }

        // Incrémenter tentatives
        $verification->increment('attempts');

        // Limiter tentatives
        if ($verification->attempts > 5) {
            return false;
        }

        // Vérifier expiration
        if ($verification->expires_at < now()) {
            return false;
        }

        // Vérifier code
        if ($verification->code !== $code) {
            return false;
        }

        // Marquer comme vérifié
        $verification->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        // Mettre à jour utilisateur
        $user = User::where('phone', $phone)->first();
        if ($user) {
            $user->update(['phone_verified_at' => now()]);
        }

        return true;
    }

    /**
     * Envoyer SMS
     */
    public function sendSMS(string $phone, string $code): void
    {
        try {
            $message = "Votre code de vérification Agri-Connect est: {$code}. Valide 10 minutes.";

            // AfricasTalking API
            $response = Http::withHeaders([
                'apiKey' => config('services.africastalking.api_key'),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post('https://api.africastalking.com/version1/messaging', [
                'username' => config('services.africastalking.username'),
                'to' => $this->formatPhone($phone),
                'message' => $message,
                'from' => config('services.africastalking.from', 'AGRICONNECT'),
            ]);

            if (!$response->successful()) {
                Log::error('SMS sending failed', [
                    'phone' => $phone,
                    'response' => $response->body(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('SMS Exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Générer code 6 chiffres
     */
    private function generateCode(): string
    {
        // En dev, code fixe pour tests
        if (config('app.env') === 'local') {
            return '123456';
        }

        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Formater numéro de téléphone
     */
    private function formatPhone(string $phone): string
    {
        // Retirer espaces et caractères spéciaux
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Ajouter indicatif pays si manquant (+237 pour Cameroun)
        if (!str_starts_with($phone, '237') && strlen($phone) === 9) {
            $phone = '237' . $phone;
        }

        return '+' . $phone;
    }

    /**
     * Renvoyer code
     */
    public function resend(string $phone): PhoneVerification
    {
        // Vérifier pas trop de demandes
        $recentVerifications = PhoneVerification::where('phone', $phone)
            ->where('created_at', '>', now()->subMinutes(5))
            ->count();

        if ($recentVerifications >= 3) {
            throw new \Exception('Trop de demandes. Attendez 5 minutes.');
        }

        return $this->generate($phone);
    }
}