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
     * Event listener mappings for the application.
     *
     * @return array
     */
    public function events()
    {
        return [
            // Tenant Created - Create database and run migrations
            Events\TenantCreated::class => [
                JobPipeline::make([
                    Jobs\CreateDatabase::class,
                    Jobs\MigrateDatabase::class,
                    // Jobs\SeedDatabase::class, // Uncomment to seed tenant database on creation
                ])->send(function (Events\TenantCreated $event) {
                    return $event->tenant;
                })->shouldBeQueued(false), // Set to true for production with queue workers
            ],

            // Tenant Deleted - Delete database (unless preserve_database or create_database was false)
            Events\TenantDeleted::class => [
                function (Events\TenantDeleted $event) {
                    $tenant = $event->tenant;

                    // Check if database should be preserved
                    $preserveDatabase = $tenant->data['preserve_database'] ?? false;

                    // Also check if database was never created in the first place
                    $databaseWasCreated = $tenant->getInternal('create_database') ?? true;

                    // Only delete database if it was created and not marked for preservation
                    if (!$preserveDatabase && $databaseWasCreated) {
                        // Delete the tenant database
                        JobPipeline::make([
                            Jobs\DeleteDatabase::class,
                        ])->send(fn() => $tenant)
                          ->shouldBeQueued(false)
                          ->toListener()($event);
                    }
                },
            ],

            // Tenancy Initialized - Bootstrap tenant context
            Events\TenancyInitialized::class => [
                Listeners\BootstrapTenancy::class,
            ],

            // Tenancy Ended - Revert to central context
            Events\TenancyEnded::class => [
                Listeners\RevertToCentralContext::class,
            ],
        ];
    }

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
    protected function bootEvents()
    {
        foreach ($this->events() as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof JobPipeline) {
                    $listener = $listener->toListener();
                }

                Event::listen($event, $listener);
            }
        }
    }
}
