<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * Implements domain-aware redirect logic for multi-tenant architecture:
     * - Central domain requests → Central admin login
     * - Tenant subdomain /customer/* routes → Customer portal login
     * - Tenant subdomain other routes → Staff portal login
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            $currentDomain = $request->getHost();
            $currentDomainWithPort = $request->getHttpHost();

            // Get central domains from config
            $centralDomains = config('tenancy.central_domains', []);

            // Check if current domain is a central domain
            $isCentralDomain = in_array($currentDomain, $centralDomains)
                            || in_array($currentDomainWithPort, $centralDomains);

            // ================================================================
            // 1. CENTRAL DOMAIN AUTHENTICATION
            // ================================================================
            // If accessing from central domain, redirect to central admin login
            if ($isCentralDomain) {
                return route('central.login');
            }

            // ================================================================
            // 2. TENANT SUBDOMAIN AUTHENTICATION
            // ================================================================
            // Not on central domain, must be tenant subdomain

            // Customer Portal routes (/customer/*)
            if ($request->is('customer/*')) {
                return route('customer.login');
            }

            // Staff Portal routes (all other tenant routes)
            return route('login');
        }
    }
}
