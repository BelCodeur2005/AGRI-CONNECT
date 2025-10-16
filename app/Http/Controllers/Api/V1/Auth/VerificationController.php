<?php

// app/Http/Controllers/Api/V1/Auth/VerificationController.php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyPhoneRequest;
use App\Http\Requests\Auth\ResendVerificationRequest;
use App\Services\Auth\PhoneVerificationService;
use App\Events\Auth\PhoneVerified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function __construct(
        private PhoneVerificationService $verificationService
    ) {}

    /**
     * Vérifier code
     */
    public function verify(VerifyPhoneRequest $request): JsonResponse
    {
        $verified = $this->verificationService->verify(
            $request->phone,
            $request->code
        );

        if (!$verified) {
            return response()->json([
                'success' => false,
                'message' => 'Code invalide ou expiré',
            ], 422);
        }

        // Événement
        $user = \App\Models\User::where('phone', $request->phone)->first();
        if ($user) {
            event(new PhoneVerified($user));
        }

        return response()->json([
            'success' => true,
            'message' => 'Téléphone vérifié avec succès',
        ]);
    }

    /**
     * Renvoyer code
     */
    public function resend(ResendVerificationRequest $request): JsonResponse
    {
        try {
            $verification = $this->verificationService->resend($request->phone);

            // Envoyer SMS
            $this->verificationService->sendSMS($request->phone, $verification->code);

            return response()->json([
                'success' => true,
                'message' => 'Code renvoyé avec succès',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 429);
        }
    }

    /**
     * Vérifier si téléphone déjà vérifié
     */
    public function check(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'is_verified' => (bool) $user->phone_verified_at,
                'phone' => $user->phone,
            ],
        ]);
    }
}