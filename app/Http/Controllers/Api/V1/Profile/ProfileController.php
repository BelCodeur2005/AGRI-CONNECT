<?php

// app/Http/Controllers/Api/V1/Profile/ProfileController.php
namespace App\Http\Controllers\Api\V1\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\CompleteProfileRequest;
use App\Http\Resources\Auth\AuthenticatedUserResource;
use App\Services\Common\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private FileUploadService $fileUploadService
    ) {}

    /**
     * Obtenir profil actuel
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new AuthenticatedUserResource($request->user()),
        ]);
    }

    /**
     * Mettre à jour profil général
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Upload photo de profil si présente
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                $this->fileUploadService->delete($user->profile_photo);
            }
            $data['profile_photo'] = $this->fileUploadService->uploadImage(
                $request->file('profile_photo'),
                'profiles'
            );
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'data' => new AuthenticatedUserResource($user->fresh()),
        ]);
    }

    /**
     * Compléter profil
     */
    public function complete(CompleteProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->getProfile();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profil introuvable',
            ], 404);
        }

        $profile->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profil complété avec succès',
            'data' => new AuthenticatedUserResource($user->fresh()),
        ]);
    }

    /**
     * Supprimer compte
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        // Vérifier qu'il n'y a pas de commandes actives
        if ($user->isProducer()) {
            $activeOrders = $user->producer->orderItems()->active()->count();
            if ($activeOrders > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer le compte avec des commandes actives',
                ], 422);
            }
        }

        if ($user->isBuyer()) {
            $activeOrders = $user->buyer->orders()->active()->count();
            if ($activeOrders > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer le compte avec des commandes actives',
                ], 422);
            }
        }

        // Révoquer tokens
        $user->tokens()->delete();

        // Soft delete
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Compte supprimé avec succès',
        ]);
    }
}
