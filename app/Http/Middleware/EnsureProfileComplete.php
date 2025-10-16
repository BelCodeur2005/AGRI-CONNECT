<?php

// app/Http/Middleware/EnsureProfileComplete.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
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

        // Vérifier si le profil est complet
        if (!$user->hasCompleteProfile()) {
            $missingFields = $user->getMissingProfileFields();

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'PROFILE_INCOMPLETE',
                    'message' => 'Veuillez compléter votre profil avant de continuer',
                    'missing_fields' => $missingFields,
                ],
            ], 403);
        }

        return $next($request);
    }
}