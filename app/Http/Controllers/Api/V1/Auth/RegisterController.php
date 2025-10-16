<?php

// app/Http/Controllers/Api/V1/Auth/RegisterController.php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\AuthenticatedUserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Inscription réussie. Vérifiez votre téléphone.',
            'data' => [
                'user' => new AuthenticatedUserResource($result['user']),
                'token' => $result['token'],
                'token_type' => 'Bearer',
                'requires_verification' => $result['requires_verification'],
            ],
        ], 201);
    }
}