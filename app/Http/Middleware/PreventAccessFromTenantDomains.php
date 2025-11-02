<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Prevent access to central routes from tenant domains
 *
 * This middleware ensures that public website and central admin routes
 * are only accessible from central domains, not tenant subdomains.
 */
class PreventAccessFromTenantDomains
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $currentDomain = $request->getHost();
        $currentDomainWithPort = $request->getHttpHost();

        // Get central domains from config
        $centralDomains = config('tenancy.central_domains', []);

        // Check if current domain is a central domain
        $isCentralDomain = in_array($currentDomain, $centralDomains)
                        || in_array($currentDomainWithPort, $centralDomains)
                        || in_array($request->getHttpHost(), $centralDomains);

        // If not a central domain, abort with 404
        if (!$isCentralDomain) {
            abort(404, 'This route is only accessible from central domains.');
        }

        return $next($request);
    }
}
