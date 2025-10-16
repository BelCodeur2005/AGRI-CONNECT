<?php

// app/Http/Controllers/Api/V1/Auth/PasswordController.php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\AuthService;
use App\Services\Auth\PhoneVerificationService;
use Illuminate\Http\JsonResponse;

class PasswordController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private PhoneVerificationService $verificationService
    ) {}

    /**
     * Changer mot de passe
     */
    public function change(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword(
            $request->user(),
            $request->current_password,
            $request->password
        );

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe modifié avec succès. Veuillez vous reconnecter.',
        ]);
    }

    /**
     * Demander réinitialisation
     */
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $verification = $this->verificationService->generate($request->phone);
        $this->verificationService->sendSMS($request->phone, $verification->code);

        return response()->json([
            'success' => true,
            'message' => 'Code de réinitialisation envoyé par SMS',
        ]);
    }

    /**
     * Réinitialiser mot de passe
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword(
            $request->phone,
            $request->code,
            $request->password
        );

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès',
        ]);
    }
}