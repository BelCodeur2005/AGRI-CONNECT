<?php

// app/Http/Middleware/LogApiRequests.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Continuer la requête
        $response = $next($request);

        // Calculer temps d'exécution
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        // Logger la requête
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => auth()->id(),
            'user_agent' => $request->userAgent(),
            'status_code' => $response->getStatusCode(),
            'execution_time_ms' => $executionTime,
        ];

        // Logger selon le statut
        if ($response->getStatusCode() >= 500) {
            Log::error('API Request Error', $logData);
        } elseif ($response->getStatusCode() >= 400) {
            Log::warning('API Request Client Error', $logData);
        } elseif ($executionTime > 1000) {
            Log::warning('API Request Slow', $logData);
        } else {
            Log::info('API Request', $logData);
        }

        // Ajouter header avec temps d'exécution
        $response->headers->set('X-Execution-Time', $executionTime . 'ms');

        return $response;
    }
}