<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectWww
{
    /**
     * Handle an incoming request.
     *
     * Redirects www to non-www for SEO canonicalization
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to public website, not admin panels or tenant subdomains
        $host = $request->getHost();

        // Check if request is using www subdomain
        if (str_starts_with($host, 'www.')) {
            // Get the non-www version
            $nonWwwHost = substr($host, 4); // Remove 'www.'

            // Build the redirect URL
            $redirectUrl = $request->getScheme() . '://' . $nonWwwHost . $request->getRequestUri();

            // 301 Permanent Redirect for SEO
            return redirect($redirectUrl, 301);
        }

        return $next($request);
    }
}
