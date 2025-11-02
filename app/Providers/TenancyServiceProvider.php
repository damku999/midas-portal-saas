<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;

class TenancyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->bootEvents();
    }

    /**
     * Register event listeners for tenancy events.
     */
    protected function bootEvents(): void
    {
        // Tenant Created - Create database and run migrations
        Event::listen(Events\TenantCreated::class, Jobs\CreateDatabase::class);
        Event::listen(Events\TenantCreated::class, Jobs\MigrateDatabase::class);

        // Optionally seed tenant database on creation
        // Event::listen(Events\TenantCreated::class, Jobs\SeedDatabase::class);

        // Tenant Deleted - Delete database
        Event::listen(Events\TenantDeleted::class, Jobs\DeleteDatabase::class);

        // Tenancy Initialized/Ended
        Event::listen(Events\TenancyInitialized::class, Listeners\BootstrapTenancy::class);
        Event::listen(Events\TenancyEnded::class, Listeners\RevertToCentralContext::class);
    }
}
