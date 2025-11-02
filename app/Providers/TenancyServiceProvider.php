<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Stancl\JobPipeline\JobPipeline;
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
        // Tenant Created - Create database and run migrations using JobPipeline
        Event::listen(Events\TenantCreated::class, function (Events\TenantCreated $event) {
            JobPipeline::make([
                Jobs\CreateDatabase::class,
                Jobs\MigrateDatabase::class,
                // Jobs\SeedDatabase::class, // Uncomment to seed tenant database on creation
            ])->send($event)->dispatch();
        });

        // Tenant Deleted - Delete database
        Event::listen(Events\TenantDeleted::class, function (Events\TenantDeleted $event) {
            JobPipeline::make([
                Jobs\DeleteDatabase::class,
            ])->send($event)->dispatch();
        });

        // Tenancy Initialized/Ended
        Event::listen(Events\TenancyInitialized::class, Listeners\BootstrapTenancy::class);
        Event::listen(Events\TenancyEnded::class, Listeners\RevertToCentralContext::class);
    }
}
