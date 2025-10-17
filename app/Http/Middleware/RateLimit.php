<?php

namespace App\Http\Middleware;

use App\Models\RateLimitAttempt;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response as ResponseInterface;

class RateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1, string $prefix = 'global'): ResponseInterface
    {
        $identifier = $this->resolveRequestIdentifier($request, $prefix);
        $key = $this->formatCacheKey($identifier, $prefix);

        // Check if rate limit is exceeded
        if ($this->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildRateLimitResponse($request, $key, $maxAttempts, $decayMinutes);
        }

        // Increment attempt counter
        $this->incrementAttempts($key, $decayMinutes);

        // Log rate limit attempt for analytics
        $this->logRateLimitAttempt($request, $identifier, $prefix);

        $response = $next($request);

        // Add rate limit headers
        $this->addRateLimitHeaders($response, $key, $maxAttempts, $decayMinutes);

        return $response;
    }

    /**
     * Resolve the identifier for rate limiting
     */
    protected function resolveRequestIdentifier(Request $request, string $prefix): string
    {
        // Use API key if available (from previous middleware)
        $apiKey = $request->attributes->get('api_key');
        if ($apiKey) {
            return "api_key:{$apiKey->id}";
        }

        // Use authenticated user if available
        if ($request->user()) {
            return "user:{$request->user()->id}";
        }

        // Fall back to IP address
        return "ip:{$request->ip()}";
    }

    /**
     * Format the cache key for rate limiting
     */
    protected function formatCacheKey(string $identifier, string $prefix): string
    {
        return "rate_limit:{$prefix}:{$identifier}";
    }

    /**
     * Check if too many attempts have been made
     */
    protected function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return Cache::get($key, 0) >= $maxAttempts;
    }

    /**
     * Increment the attempt counter
     */
    protected function incrementAttempts(string $key, int $decayMinutes): void
    {
        $attempts = Cache::get($key, 0) + 1;
        $ttl = $decayMinutes * 60; // Convert to seconds

        Cache::put($key, $attempts, $ttl);
    }

    /**
     * Get the current number of attempts
     */
    protected function getAttempts(string $key): int
    {
        return Cache::get($key, 0);
    }

    /**
     * Calculate when the rate limit will reset
     */
    protected function getTimeUntilReset(string $key, int $decayMinutes): int
    {
        $ttl = Cache::getStore()->ttl($key);

        return $ttl > 0 ? $ttl : $decayMinutes * 60;
    }

    /**
     * Log rate limit attempt for monitoring
     */
    protected function logRateLimitAttempt(Request $request, string $identifier, string $prefix): void
    {
        $endpoint = $request->route()?->uri() ?? $request->path();
        $identifierType = explode(':', $identifier)[0];
        $identifierValue = explode(':', $identifier)[1] ?? $identifier;

        // Use database for persistent logging
        RateLimitAttempt::updateOrCreate([
            'identifier' => $identifierValue,
            'identifier_type' => $identifierType,
            'endpoint' => $endpoint,
            'window_start' => now()->startOfMinute(),
        ], [
            'attempts' => \DB::raw('attempts + 1'),
            'last_attempt' => now(),
        ]);
    }

    /**
     * Build the rate limit exceeded response
     */
    protected function buildRateLimitResponse(Request $request, string $key, int $maxAttempts, int $decayMinutes): Response
    {
        $timeUntilReset = $this->getTimeUntilReset($key, $decayMinutes);

        return response()->json([
            'error' => 'Rate Limit Exceeded',
            'message' => "Too many requests. Limit of {$maxAttempts} requests per {$decayMinutes} minute(s) exceeded.",
            'status_code' => 429,
            'rate_limit' => [
                'limit' => $maxAttempts,
                'remaining' => 0,
                'reset_in_seconds' => $timeUntilReset,
                'reset_at' => now()->addSeconds($timeUntilReset)->timestamp,
            ],
        ], 429, [
            'Retry-After' => $timeUntilReset,
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
            'X-RateLimit-Reset' => now()->addSeconds($timeUntilReset)->timestamp,
        ]);
    }

    /**
     * Add rate limit headers to response
     */
    protected function addRateLimitHeaders($response, string $key, int $maxAttempts, int $decayMinutes): void
    {
        $attempts = $this->getAttempts($key);
        $remaining = max(0, $maxAttempts - $attempts);
        $resetTime = now()->addSeconds($this->getTimeUntilReset($key, $decayMinutes))->timestamp;

        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $remaining);
        $response->headers->set('X-RateLimit-Reset', $resetTime);
    }
}
