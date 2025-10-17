<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // ✅ Ajout des routes API
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware globaux
        $middleware->api(prepend: [
            \App\Http\Middleware\LogApiRequests::class,
        ]);

        // Alias de middleware
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'verified.phone' => \App\Http\Middleware\VerifiedPhoneMiddleware::class,
            'profile.complete' => \App\Http\Middleware\EnsureProfileComplete::class,
            'order.owner' => \App\Http\Middleware\CheckOrderOwnership::class,
            'throttle.role' => \App\Http\Middleware\ThrottleByRole::class,
        ]);

        // Rate limiting par défaut
        $middleware->throttleApi('throttle.role');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Gestion des exceptions personnalisées
        $exceptions->renderable(function (\App\Exceptions\BaseAgriConnectException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => $e->getErrorCode(),
                        'message' => $e->getMessage(),
                        'context' => $e->getContext(),
                    ],
                ], $e->getStatusCode());
            }
        });

        // Gestion des 404
        $exceptions->renderable(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RESOURCE_NOT_FOUND',
                        'message' => 'Ressource introuvable',
                    ],
                ], 404);
            }
        });

        // Gestion des erreurs de validation
        $exceptions->renderable(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'Erreur de validation',
                        'errors' => $e->errors(),
                    ],
                ], 422);
            }
        });

        // Gestion des erreurs d'autorisation
        $exceptions->renderable(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => $e->getMessage(),
                    ],
                ], 403);
            }
        });

        // Gestion des erreurs d'authentification
        $exceptions->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHENTICATED',
                        'message' => 'Non authentifié',
                    ],
                ], 401);
            }
        });
    })
    ->create();