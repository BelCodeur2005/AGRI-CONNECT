<?php

// app/Http/Middleware/VerifiedPhoneMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifiedPhoneMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié',
            ], 401);
        }

        // Vérifier si le téléphone est vérifié
        if (!$user->phone_verified_at) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'PHONE_NOT_VERIFIED',
                    'message' => 'Veuillez vérifier votre numéro de téléphone avant de continuer',
                    'action_required' => 'phone_verification',
                    'phone' => $user->phone,
                ],
            ], 403);
        }

        return $next($request);
    }
}