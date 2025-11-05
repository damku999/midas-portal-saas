<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * DynamicConfigServiceProvider
 *
 * NOTE: This provider has been replaced by App\Tenancy\Bootstrappers\DynamicConfigBootstrapper
 * which is registered in config/tenancy.php. The bootstrapper approach ensures that
 * tenant-specific configurations are loaded immediately when tenancy is initialized.
 *
 * This provider is kept for backward compatibility but is no longer used.
 */
class DynamicConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Configuration loading is now handled by DynamicConfigBootstrapper
        // See: app/Tenancy/Bootstrappers/DynamicConfigBootstrapper.php
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }
}
