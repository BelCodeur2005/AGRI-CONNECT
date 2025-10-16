<?php

// app/Http/Controllers/Api/V1/Auth/LogoutController.php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $allDevices = $request->boolean('all_devices', false);
        
        $this->authService->logout($request->user(), $allDevices);

        return response()->json([
            'success' => true,
            'message' => $allDevices 
                ? 'Déconnexion de tous les appareils réussie' 
                : 'Déconnexion réussie',
        ]);
    }
}
