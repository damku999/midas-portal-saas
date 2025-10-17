<?php

namespace App\Modules;

use App\Modules\Customer\Contracts\CustomerServiceInterface;
use App\Modules\Customer\Services\CustomerService;
use App\Modules\Notification\Contracts\NotificationServiceInterface;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Policy\Contracts\PolicyServiceInterface;
use App\Modules\Policy\Services\PolicyService;
use App\Modules\Quotation\Contracts\QuotationServiceInterface;
use App\Modules\Quotation\Services\QuotationService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        CustomerServiceInterface::class => CustomerService::class,
        QuotationServiceInterface::class => QuotationService::class,
        NotificationServiceInterface::class => NotificationService::class,
        PolicyServiceInterface::class => PolicyService::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerModuleServices();
        $this->registerModuleRepositories();
        $this->registerModuleEvents();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadModuleRoutes();
        $this->loadModuleMigrations();
        $this->registerModuleEventListeners();
    }

    /**
     * Register module service bindings
     */
    private function registerModuleServices(): void
    {
        // Customer Module - Override existing binding
        $this->app->bind(
            \App\Contracts\Services\CustomerServiceInterface::class,
            \App\Services\CustomerService::class
        );

        // Quotation Module
        $this->app->bind(
            QuotationServiceInterface::class,
            function ($app) {
                return new QuotationService(
                    $app->make(\App\Services\PdfGenerationService::class),
                    $app->make(\App\Contracts\Repositories\QuotationRepositoryInterface::class)
                );
            }
        );

        // Notification Module
        $this->app->singleton(NotificationServiceInterface::class, NotificationService::class);

        // Policy Module
        $this->app->bind(PolicyServiceInterface::class, \App\Modules\Policy\Services\PolicyService::class);
    }

    /**
     * Register module repository bindings
     */
    private function registerModuleRepositories(): void
    {
        // Module-specific repositories will be registered here
        // For now, they use the existing global repositories
    }

    /**
     * Register module event system
     */
    private function registerModuleEvents(): void
    {
        // Cross-module event handlers will be registered here
        $this->registerCustomerModuleEvents();
        $this->registerQuotationModuleEvents();
        $this->registerNotificationModuleEvents();
    }

    /**
     * Load module-specific routes
     */
    private function loadModuleRoutes(): void
    {
        // Load module API routes with proper middleware and prefixes
        // These routes are loaded under /api/v2 to avoid conflicts with existing /api/v1 routes
        Route::prefix('api/v2')
            ->middleware(['api', 'throttle:api'])
            ->group(function () {
                // Load Customer Module API routes
                if (file_exists($customerApiRoutes = base_path('routes/api/customer.php'))) {
                    $this->loadRoutesFrom($customerApiRoutes);
                }

                // Load Quotation Module API routes
                if (file_exists($quotationApiRoutes = base_path('routes/api/quotation.php'))) {
                    $this->loadRoutesFrom($quotationApiRoutes);
                }

                // Load Notification Module API routes
                if (file_exists($notificationApiRoutes = base_path('routes/api/notification.php'))) {
                    $this->loadRoutesFrom($notificationApiRoutes);
                }
            });
    }

    /**
     * Load module migrations
     */
    private function loadModuleMigrations(): void
    {
        $this->loadMigrationsFrom([
            base_path('app/Modules/Customer/Database/Migrations'),
            base_path('app/Modules/Quotation/Database/Migrations'),
            base_path('app/Modules/Notification/Database/Migrations'),
            base_path('app/Modules/Policy/Database/Migrations'),
        ]);
    }

    /**
     * Register event listeners for modules
     */
    private function registerModuleEventListeners(): void
    {
        // Cross-module communication via events
        // Note: Most event listeners are registered in EventServiceProvider

        // Customer welcome notification: Already registered in EventServiceProvider
        // Event: \App\Events\Customer\CustomerRegistered
        // Listener: \App\Listeners\Customer\SendOnboardingWhatsApp
        // This listener sends WhatsApp onboarding message using customer_welcome template

        // Quotation WhatsApp notification (active)
        $this->app['events']->listen(
            \App\Events\Quotation\QuotationGenerated::class,
            [\App\Listeners\Quotation\SendQuotationWhatsApp::class, 'handle']
        );

        // Policy renewal notification: Already registered in EventServiceProvider
        // Event: \App\Events\Insurance\PolicyExpiringWarning
        // Listener: \App\Listeners\Insurance\SendPolicyRenewalReminder
        // This listener sends multi-channel renewal reminders (Email + WhatsApp)
    }

    /**
     * Register Customer Module events
     */
    private function registerCustomerModuleEvents(): void
    {
        // Customer module specific event handlers
    }

    /**
     * Register Quotation Module events
     */
    private function registerQuotationModuleEvents(): void
    {
        // Quotation module specific event handlers
    }

    /**
     * Register Notification Module events
     */
    private function registerNotificationModuleEvents(): void
    {
        // Notification module specific event handlers
    }
}
