<?php

// app/Http/Controllers/Api/V1/Profile/BuyerProfileController.php
namespace App\Http\Controllers\Api\V1\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateBuyerProfileRequest;
use App\Http\Resources\Profile\BuyerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuyerProfileController extends Controller
{
    /**
     * Obtenir profil acheteur
     */
    public function show(Request $request): JsonResponse
    {
        $buyer = $request->user()->buyer;

        if (!$buyer) {
            return response()->json([
                'success' => false,
                'message' => 'Profil acheteur introuvable',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new BuyerResource($buyer->load('location')),
        ]);
    }

    /**
     * Mettre à jour profil acheteur
     */
    public function update(UpdateBuyerProfileRequest $request): JsonResponse
    {
        $buyer = $request->user()->buyer;
        $buyer->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profil acheteur mis à jour',
            'data' => new BuyerResource($buyer->fresh()),
        ]);
    }
}