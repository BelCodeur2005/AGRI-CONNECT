<?php

// app/Http/Controllers/Api/V1/Profile/ProducerProfileController.php
namespace App\Http\Controllers\Api\V1\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProducerProfileRequest;
use App\Http\Resources\Profile\ProducerResource;
use App\Services\Common\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProducerProfileController extends Controller
{
    public function __construct(
        private FileUploadService $fileUploadService
    ) {}

    /**
     * Obtenir profil producteur
     */
    public function show(Request $request): JsonResponse
    {
        $producer = $request->user()->producer;

        if (!$producer) {
            return response()->json([
                'success' => false,
                'message' => 'Profil producteur introuvable',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ProducerResource($producer->load('location')),
        ]);
    }

    /**
     * Mettre à jour profil producteur
     */
    public function update(UpdateProducerProfileRequest $request): JsonResponse
    {
        $producer = $request->user()->producer;
        $data = $request->validated();

        // Upload ID card photo si présente
        if ($request->hasFile('id_card_photo')) {
            if ($producer->id_card_photo) {
                $this->fileUploadService->delete($producer->id_card_photo);
            }
            $data['id_card_photo'] = $this->fileUploadService->uploadFile(
                $request->file('id_card_photo'),
                'id-cards'
            );
        }

        $producer->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profil producteur mis à jour',
            'data' => new ProducerResource($producer->fresh()),
        ]);
    }
}