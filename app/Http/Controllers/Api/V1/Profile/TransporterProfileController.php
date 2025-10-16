<?php

// app/Http/Controllers/Api/V1/Profile/TransporterProfileController.php
namespace App\Http\Controllers\Api\V1\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateTransporterProfileRequest;
use App\Http\Resources\Profile\TransporterResource;
use App\Services\Common\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransporterProfileController extends Controller
{
    public function __construct(
        private FileUploadService $fileUploadService
    ) {}

    /**
     * Obtenir profil transporteur
     */
    public function show(Request $request): JsonResponse
    {
        $transporter = $request->user()->transporter;

        if (!$transporter) {
            return response()->json([
                'success' => false,
                'message' => 'Profil transporteur introuvable',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new TransporterResource($transporter),
        ]);
    }

    /**
     * Mettre à jour profil transporteur
     */
    public function update(UpdateTransporterProfileRequest $request): JsonResponse
    {
        $transporter = $request->user()->transporter;
        $data = $request->validated();

        // Upload vehicle photo
        if ($request->hasFile('vehicle_photo')) {
            if ($transporter->vehicle_photo) {
                $this->fileUploadService->delete($transporter->vehicle_photo);
            }
            $data['vehicle_photo'] = $this->fileUploadService->uploadImage(
                $request->file('vehicle_photo'),
                'vehicles'
            );
        }

        // Upload driver license photo
        if ($request->hasFile('driver_license_photo')) {
            if ($transporter->driver_license_photo) {
                $this->fileUploadService->delete($transporter->driver_license_photo);
            }
            $data['driver_license_photo'] = $this->fileUploadService->uploadFile(
                $request->file('driver_license_photo'),
                'licenses'
            );
        }

        $transporter->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profil transporteur mis à jour',
            'data' => new TransporterResource($transporter->fresh()),
        ]);
    }

    /**
     * Toggle disponibilité
     */
    public function toggleAvailability(Request $request): JsonResponse
    {
        $transporter = $request->user()->transporter;
        $transporter->update(['is_available' => !$transporter->is_available]);

        return response()->json([
            'success' => true,
            'message' => $transporter->is_available 
                ? 'Vous êtes maintenant disponible' 
                : 'Vous êtes maintenant indisponible',
            'data' => ['is_available' => $transporter->is_available],
        ]);
    }
}