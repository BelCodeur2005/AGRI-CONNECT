<?php

// app/Http/Middleware/RoleMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param array<string> $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié',
            ], 401);
        }

        // Convertir les rôles en UserRole enums
        $allowedRoles = array_map(fn($role) => UserRole::from($role), $roles);

        // Vérifier si l'utilisateur a l'un des rôles autorisés
        if (!in_array($user->role, $allowedRoles)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED_ROLE',
                    'message' => 'Vous n\'avez pas les permissions nécessaires pour accéder à cette ressource',
                    'required_roles' => $roles,
                    'your_role' => $user->role->value,
                ],
            ], 403);
        }

        return $next($request);
    }
}