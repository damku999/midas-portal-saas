<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // User Events
        \App\Events\User\UserRegistered::class => [
            \App\Listeners\User\SendUserOnboardingWhatsApp::class, // Send WhatsApp welcome message to newly registered users
        ],

        // Customer Events
        \App\Events\Customer\CustomerRegistered::class => [
            // SendWelcomeEmail is now handled synchronously in CustomerService
            \App\Listeners\Customer\CreateCustomerAuditLog::class,
            \App\Listeners\Customer\NotifyAdminOfRegistration::class,
            \App\Listeners\Customer\SendOnboardingWhatsApp::class, // Send WhatsApp welcome message using customer_welcome template
        ],

        \App\Events\Customer\CustomerEmailVerified::class => [
            \App\Listeners\Customer\CreateCustomerAuditLog::class,
        ],

        \App\Events\Customer\CustomerProfileUpdated::class => [
            \App\Listeners\Customer\CreateCustomerAuditLog::class,
        ],

        // Quotation Events
        \App\Events\Quotation\QuotationRequested::class => [
            \App\Listeners\Customer\CreateCustomerAuditLog::class,
        ],

        \App\Events\Quotation\QuotationGenerated::class => [
            \App\Listeners\Quotation\GenerateQuotationPDF::class,
            \App\Listeners\Quotation\SendQuotationWhatsApp::class,
        ],

        // Insurance Policy Events
        \App\Events\Insurance\PolicyCreated::class => [
            \App\Listeners\Customer\CreateCustomerAuditLog::class,
        ],

        \App\Events\Insurance\PolicyRenewed::class => [
            \App\Listeners\Customer\CreateCustomerAuditLog::class,
        ],

        \App\Events\Insurance\PolicyExpiringWarning::class => [
            \App\Listeners\Insurance\SendPolicyRenewalReminder::class,
        ],

        // Audit Events
        \App\Events\Audit\CustomerActionLogged::class => [
            // Future listeners for security monitoring, analytics, etc.
        ],

        // Document Events
        \App\Events\Document\PDFGenerationRequested::class => [
            // PDF generation will be handled by dedicated service
        ],

        // Legacy Events (to be phased out)
        \App\Events\CustomerCreated::class => [
            \App\Listeners\SendWelcomeEmail::class,
        ],
        \App\Events\PolicyExpiring::class => [
            \App\Listeners\SendPolicyReminderNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
