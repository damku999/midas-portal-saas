<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        // Route model bindings
        Route::bind('member', function ($value) {
            return \App\Models\Customer::findOrFail($value);
        });

        $this->routes(function () {
            /*
            |--------------------------------------------------------------------------
            | Route Loading Order (CRITICAL - DO NOT CHANGE)
            |--------------------------------------------------------------------------
            |
            | Routes are loaded in this specific order to prevent conflicts:
            | 1. Central Admin Routes (/midas-admin/*) - NO tenant middleware
            | 2. Public Website Routes (/, /features, etc.) - Central domains only
            | 3. Tenant Staff Portal (ALL tenant business routes) - Tenant middleware
            | 4. Customer Portal (/customer/*) - Tenant middleware
            |
            */

            // ====================================================================
            // 1. CENTRAL ADMIN ROUTES
            // ====================================================================
            // Accessible ONLY on central domains (midastech.in, midastech.testing.in)
            // NO tenant identification middleware
            // Guard: 'central'
            // ====================================================================
            Route::prefix('midas-admin')
                ->middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/central.php'));

            // ====================================================================
            // 2. PUBLIC WEBSITE ROUTES (MUST LOAD BEFORE TENANT ROUTES!)
            // ====================================================================
            // Accessible ONLY on central domains
            // Uses domain() to restrict to central domains at route registration level
            // This prevents tenant middleware from even attempting to initialize
            // NO authentication required
            // Loaded BEFORE tenant routes to prevent route conflicts on '/' path
            // ====================================================================
            foreach (config('tenancy.central_domains') as $domain) {
                Route::domain($domain)
                    ->middleware('web')
                    ->namespace($this->namespace)
                    ->group(base_path('routes/public.php'));
            }

            // ====================================================================
            // 3. TENANT STAFF PORTAL ROUTES
            // ====================================================================
            // Accessible ONLY on tenant subdomains (tenant.midastech.testing.in)
            // Middleware stack:
            // - 'web' → Session, CSRF, cookies
            // - 'universal' → InitializeTenancyByDomain (identifies tenant)
            // - 'tenant' → PreventAccessFromCentralDomains (blocks central access)
            // Guard: 'web' (default)
            // ====================================================================
            Route::middleware(['web', 'universal', 'tenant'])
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));

            // ====================================================================
            // 4. CUSTOMER PORTAL ROUTES
            // ====================================================================
            // Accessible ONLY on tenant subdomains (/customer/*)
            // Middleware stack: Same as tenant staff portal
            // Guard: 'customer'
            // ====================================================================
            Route::middleware(['web', 'universal', 'tenant'])
                ->namespace($this->namespace)
                ->group(base_path('routes/customer.php'));

            // ====================================================================
            // 5. API ROUTES (if needed in future)
            // ====================================================================
            // Currently disabled - web application only
            // Route::prefix('api')
            //     ->middleware('api')
            //     ->namespace($this->namespace)
            //     ->group(base_path('routes/api.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
