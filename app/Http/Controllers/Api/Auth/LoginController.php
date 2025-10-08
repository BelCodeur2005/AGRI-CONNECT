<?php

// app/Http/Controllers/Api/Auth/LoginController.php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        // Trouver l'utilisateur par téléphone
        $user = User::where('phone', $request->phone)->first();

        // Vérifier si utilisateur existe et mot de passe correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Téléphone ou mot de passe incorrect',
            ], 401);
        }

        // Vérifier si compte actif
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte a été désactivé. Contactez le support.',
            ], 403);
        }

        // Révoquer anciens tokens si demandé
        if ($request->revoke_other_tokens) {
            $user->tokens()->delete();
        }

        // Mettre à jour FCM token pour notifications push
        if ($request->fcm_token) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        // Générer nouveau token
        $token = $user->createToken('auth_token', [$user->role->value])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'role' => $user->role->value,
                    'is_verified' => $user->is_verified,
                    'phone_verified' => (bool) $user->phone_verified_at,
                    'profile_photo_url' => $user->profile_photo_url,
                ],
                'profile' => $user->getProfile(),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }
}