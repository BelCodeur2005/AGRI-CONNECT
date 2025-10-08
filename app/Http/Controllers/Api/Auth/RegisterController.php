<?php

// app/Http/Controllers/Api/Auth/RegisterController.php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\Producer;
use App\Models\Buyer;
use App\Models\Transporter;
use App\Models\PhoneVerification;
use App\Enums\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Créer l'utilisateur
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'is_active' => true,
            ]);

            // Créer le profil selon le rôle
            $this->createProfile($user, $request);

            // Générer code vérification téléphone
            $verification = PhoneVerification::generate($user->phone);

            // TODO: Envoyer SMS avec code (intégration AfricasTalking)
            // $this->sendVerificationSMS($user->phone, $verification->code);

            // Générer token API
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inscription réussie. Un code de vérification a été envoyé à votre téléphone.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'role' => $user->role->value,
                        'is_verified' => $user->is_verified,
                        'phone_verified' => (bool) $user->phone_verified_at,
                    ],
                    'profile' => $user->getProfile(),
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'inscription',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function createProfile(User $user, RegisterRequest $request): void
    {
        match($user->role) {
            UserRole::PRODUCER => Producer::create([
                'user_id' => $user->id,
                'location_id' => $request->location_id,
                'farm_name' => $request->farm_name,
                'farm_address' => $request->farm_address,
            ]),
            
            UserRole::BUYER => Buyer::create([
                'user_id' => $user->id,
                'location_id' => $request->location_id,
                'business_name' => $request->business_name,
                'business_type' => $request->business_type ?? 'restaurant',
                'delivery_address' => $request->delivery_address,
            ]),
            
            UserRole::TRANSPORTER => Transporter::create([
                'user_id' => $user->id,
                'vehicle_type' => $request->vehicle_type,
                'vehicle_registration' => $request->vehicle_registration,
                'driver_license_number' => $request->driver_license_number,
                'max_capacity_kg' => $request->max_capacity_kg,
            ]),
            
            default => null,
        };
    }

    public function verifyPhone(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $verification = PhoneVerification::where('phone', $request->phone)
            ->where('is_verified', false)
            ->latest()
            ->first();

        if (!$verification) {
            return response()->json([
                'success' => false,
                'message' => 'Code de vérification invalide ou expiré',
            ], 400);
        }

        if ($verification->verify($request->code)) {
            // Mettre à jour l'utilisateur
            $user = User::where('phone', $request->phone)->first();
            $user->update(['phone_verified_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Téléphone vérifié avec succès',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Code de vérification incorrect',
        ], 400);
    }

    public function resendVerificationCode(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $verification = PhoneVerification::generate($request->phone);

        // TODO: Envoyer SMS
        // $this->sendVerificationSMS($request->phone, $verification->code);

        return response()->json([
            'success' => true,
            'message' => 'Code de vérification renvoyé',
        ]);
    }
}