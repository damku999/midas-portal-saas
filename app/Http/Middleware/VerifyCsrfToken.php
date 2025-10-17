<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // API webhooks (if any) - be very restrictive
        // 'api/webhooks/*',
    ];

    /**
     * Enhanced CSRF token verification with security logging
     */
    protected function tokensMatch($request)
    {
        $result = parent::tokensMatch($request);

        // Log CSRF verification attempts
        if (! $result) {
            $this->logCsrfFailure($request);
        }

        return $result;
    }

    /**
     * Handle invalid CSRF token
     */
    protected function addCookieToResponse($request, $response)
    {
        $response = parent::addCookieToResponse($request, $response);

        // Add additional security headers for CSRF protection
        if ($response instanceof Response) {
            $response->headers->set('X-CSRF-Protection', 'enabled');
        }

        return $response;
    }

    /**
     * Log CSRF verification failures for security monitoring
     */
    private function logCsrfFailure($request): void
    {
        Log::channel('security')->warning('CSRF Token Verification Failed', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'referer' => $request->header('Referer'),
            'csrf_token' => $request->input('_token'),
            'timestamp' => now()->toISOString(),
        ]);

        // Store in security events table
        \DB::table('security_events')->insert([
            'event_type' => 'csrf_token_mismatch',
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'context' => json_encode([
                'referer' => $request->header('Referer'),
                'csrf_token_provided' => ! empty($request->input('_token')),
            ]),
            'severity' => 'medium',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Determine if the session and input CSRF tokens match with enhanced validation
     */
    protected function isReading($request)
    {
        $isReading = parent::isReading($request);

        // Additional validation for state-changing operations
        if (! $isReading) {
            $this->validateStateChangingRequest($request);
        }

        return $isReading;
    }

    /**
     * Additional validation for state-changing requests
     */
    private function validateStateChangingRequest($request): void
    {
        // Validate Origin header for additional CSRF protection
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');

        if ($origin && ! $this->isValidOrigin($origin, $request)) {
            Log::channel('security')->warning('Invalid Origin header detected', [
                'origin' => $origin,
                'expected_host' => $request->getHost(),
                'url' => $request->fullUrl(),
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
            ]);
        }

        // Validate Referer header
        if ($referer && ! $this->isValidReferer($referer, $request)) {
            Log::channel('security')->warning('Suspicious Referer header detected', [
                'referer' => $referer,
                'expected_host' => $request->getHost(),
                'url' => $request->fullUrl(),
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
            ]);
        }
    }

    /**
     * Validate Origin header
     */
    private function isValidOrigin(string $origin, $request): bool
    {
        $parsedOrigin = parse_url($origin);
        $expectedHost = $request->getHost();

        return isset($parsedOrigin['host']) && $parsedOrigin['host'] === $expectedHost;
    }

    /**
     * Validate Referer header
     */
    private function isValidReferer(string $referer, $request): bool
    {
        $parsedReferer = parse_url($referer);
        $expectedHost = $request->getHost();

        return isset($parsedReferer['host']) && $parsedReferer['host'] === $expectedHost;
    }
}
