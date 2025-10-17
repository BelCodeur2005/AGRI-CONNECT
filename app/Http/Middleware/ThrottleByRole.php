<?php

// app/Http/Middleware/ThrottleByRole.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Str;

class ThrottleByRole
{
    protected RateLimiter $limiter;

    /**
     * Rate limits par rôle (requêtes par minute)
     */
    protected array $limits = [
        'admin' => 300,       // Admins : 300 req/min
        'producer' => 120,    // Producteurs : 120 req/min
        'buyer' => 180,       // Acheteurs : 180 req/min
        'transporter' => 150, // Transporteurs : 150 req/min
        'guest' => 60,        // Non authentifiés : 60 req/min
    ];

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);
        $maxAttempts = $this->resolveMaxAttempts($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildTooManyAttemptsResponse($key, $maxAttempts);
        }

        $this->limiter->hit($key, 60); // 60 secondes

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * Résoudre la signature de la requête
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $user = $request->user();
        
        if ($user) {
            return Str::slug($user->role->value . '|' . $user->id);
        }

        return Str::slug('guest|' . $request->ip());
    }

    /**
     * Résoudre le nombre max de tentatives selon le rôle
     */
    protected function resolveMaxAttempts(Request $request): int
    {
        $user = $request->user();

        if (!$user) {
            return $this->limits['guest'];
        }

        return $this->limits[$user->role->value] ?? $this->limits['guest'];
    }

    /**
     * Calculer tentatives restantes
     */
    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        return $maxAttempts - $this->limiter->attempts($key) + 1;
    }

    /**
     * Construire réponse "trop de requêtes"
     */
    protected function buildTooManyAttemptsResponse(string $key, int $maxAttempts): Response
    {
        $retryAfter = $this->limiter->availableIn($key);

        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'TOO_MANY_REQUESTS',
                'message' => 'Trop de requêtes. Veuillez réessayer dans ' . $retryAfter . ' secondes.',
                'retry_after' => $retryAfter,
            ],
        ], 429)->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
            'Retry-After' => $retryAfter,
            'X-RateLimit-Reset' => now()->addSeconds($retryAfter)->timestamp,
        ]);
    }

    /**
     * Ajouter headers de rate limiting
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remainingAttempts),
        ]);

        return $response;
    }
}