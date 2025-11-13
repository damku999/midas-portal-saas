<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SECURITY FIX #16: HTTPS Enforcement Middleware
 *
 * Forces all HTTP requests to redirect to HTTPS in production environment.
 * This prevents man-in-the-middle attacks and ensures data is encrypted in transit.
 */
class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only enforce HTTPS in production environment
        if (!app()->environment('local', 'testing') && !$request->secure()) {
            // Log HTTP request attempt in production
            \Log::warning('HTTP request redirected to HTTPS', [
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Redirect to HTTPS version
            return redirect()->secure($request->getRequestUri(), 301);
        }

        // Add security headers to all responses
        $response = $next($request);

        // Force HTTPS for future requests (HSTS)
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
