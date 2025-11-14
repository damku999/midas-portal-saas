<?php

namespace App\Tenancy\Bootstrappers;

use Illuminate\Support\Facades\URL;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class UrlBootstrapper implements TenancyBootstrapper
{
    public function bootstrap(Tenant $tenant)
    {
        // Get the current domain
        $domain = $tenant->domains->first();

        if ($domain) {
            // Check if running on port 8085
            $port = request()->getPort();
            $scheme = request()->getScheme();

            // Build URL with port if not standard (80/443)
            if ($port && !in_array($port, [80, 443])) {
                $url = "{$scheme}://{$domain->domain}:{$port}";
            } else {
                $url = "{$scheme}://{$domain->domain}";
            }

            URL::forceRootUrl($url);
            URL::forceScheme($scheme);
        }
    }

    public function revert()
    {
        // Reset URL configuration
        URL::forceRootUrl(config('app.url'));
    }
}
