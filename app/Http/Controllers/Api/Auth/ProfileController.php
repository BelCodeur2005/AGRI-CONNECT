<?php

// app/Http/Controllers/Api/Auth/ProfileController.php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
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
                    'created_at' => $user->created_at,
                ],
                'profile' => $user->getProfile(),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'profile_photo' => 'sometimes|image|max:2048', // 2MB max
        ]);

        // Upload photo si présente
        if ($request->hasFile('profile_photo')) {
            // Supprimer ancienne photo
            if ($user->profile_photo) {
                Storage::delete($user->profile_photo);
            }

            $path = $request->file('profile_photo')->store('profiles', 'public');
            $validatedData['profile_photo'] = $path;
        }

        $user->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour',
            'data' => [
                'user' => $user->fresh(),
            ],
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        // Vérifier ancien mot de passe
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mot de passe actuel incorrect',
            ], 400);
        }

        // Mettre à jour mot de passe
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        // Révoquer tous les anciens tokens
        $user->tokens()->delete();

        // Créer nouveau token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe modifié avec succès',
            'data' => [
                'token' => $token,
            ],
        ]);
    }

    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $request->user()->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token FCM mis à jour',
        ]);
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mot de passe incorrect',
            ], 400);
        }

        // Soft delete
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Compte supprimé avec succès',
        ]);
    }
}