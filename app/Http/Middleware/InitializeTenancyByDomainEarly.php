<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

/**
 * Initialize tenancy by domain BEFORE session middleware.
 *
 * This middleware runs in the global middleware stack to ensure tenancy
 * is initialized before the session tries to load authenticated users.
 *
 * It skips initialization for central domains to prevent breaking
 * central admin routes.
 */
class InitializeTenancyByDomainEarly extends InitializeTenancyByDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $currentDomain = $request->getHost();
        $centralDomains = config('tenancy.central_domains', []);

        // Skip tenancy initialization for central domains
        if (in_array($currentDomain, $centralDomains)) {
            return $next($request);
        }

        // For tenant domains, initialize tenancy
        return parent::handle($request, $next);
    }
}
