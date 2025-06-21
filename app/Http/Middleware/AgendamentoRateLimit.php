<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpFoundation\Response;

class AgendamentoRateLimit
{
    protected RateLimiter $limiter;

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
        
        // Limite: 3 tentativas por hora por IP
        $maxAttempts = 3;
        $decayMinutes = 60;
        
        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = $this->limiter->availableIn($key);
            
            return response()->json([
                'success' => false,
                'message' => 'Muitas tentativas de agendamento. Tente novamente em ' . ceil($retryAfter / 60) . ' minutos.',
                'retry_after' => $retryAfter
            ], 429);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Se o agendamento foi bem-sucedido, não contar como tentativa falhada
        if ($response->getStatusCode() === 201) {
            $this->limiter->clear($key);
        }

        return $response;
    }

    /**
     * Resolve the signature for the request.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        return sha1(
            'agendamento_' . $request->ip() . '|' . $request->userAgent()
        );
    }
}