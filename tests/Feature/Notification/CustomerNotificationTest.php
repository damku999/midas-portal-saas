<?php

namespace Tests\Feature\Notification;

use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\NotificationTemplate;
use App\Models\NotificationType;
use App\Services\TemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for Customer Notification flows
 *
 * Tests customer welcome and birthday wish workflows
 */
class CustomerNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create common app settings
        $this->createAppSettings();
    }

    // =======================================================
    // CUSTOMER WELCOME FLOW TESTS
    // =======================================================

    /** @test */
    public function it_sends_welcome_notification_on_customer_creation()
    {
        $this->createWelcomeTemplate();

        $customer = Customer::factory()->create([
            'name' => 'John Doe',
            'mobile_number' => '9876543210',
            'email' => 'john@example.com',
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromCustomer('customer_welcome', 'whatsapp', $customer);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('John Doe', $rendered);
        $this->assertStringContainsString('Welcome', $rendered);
    }

    /** @test */
    public function it_includes_company_details_in_welcome_message()
    {
        $this->createWelcomeTemplate();

        $customer = Customer::factory()->create(['name' => 'Alice Smith']);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromCustomer('customer_welcome', 'whatsapp', $customer);

        $this->assertStringContainsString('Midas Insurance', $rendered);
        $this->assertStringContainsString('+91 98765 43210', $rendered);
    }

    /** @test */
    public function it_includes_portal_url_in_welcome_message()
    {
        $this->createWelcomeTemplate();

        AppSetting::factory()->create([
            'category' => 'application',
            'key' => 'portal_url',
            'value' => 'https://portal.midas.com',
            'is_active' => true,
        ]);

        $customer = Customer::factory()->create(['name' => 'Bob Johnson']);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromCustomer('customer_welcome', 'whatsapp', $customer);

        $this->assertStringContainsString('https://portal.midas.com', $rendered);
    }

    // =======================================================
    // BIRTHDAY WISH FLOW TESTS
    // =======================================================

    /** @test */
    public function it_sends_birthday_wish_notification()
    {
        $this->createBirthdayTemplate();

        $customer = Customer::factory()->create([
            'name' => 'Birthday Person',
            'date_of_birth' => '1990-01-15',
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromCustomer('birthday_wish', 'whatsapp', $customer);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('Birthday Person', $rendered);
        $this->assertStringContainsString('Happy Birthday', $rendered);
    }

    /** @test */
    public function it_includes_formatted_date_in_birthday_message()
    {
        $this->createBirthdayTemplate();

        $customer = Customer::factory()->create([
            'name' => 'Test User',
            'date_of_birth' => '1990-01-15',
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromCustomer('birthday_wish', 'whatsapp', $customer);

        // Date should be formatted as d-M-Y
        $this->assertStringContainsString('15-Jan-1990', $rendered);
    }

    // =======================================================
    // ANNIVERSARY FLOW TESTS
    // =======================================================

    /** @test */
    public function it_sends_wedding_anniversary_notification()
    {
        $this->createAnniversaryTemplate('wedding');

        $customer = Customer::factory()->create([
            'name' => 'Happy Couple',
            'wedding_anniversary_date' => '2015-02-14',
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromCustomer('wedding_anniversary', 'whatsapp', $customer);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('Happy Couple', $rendered);
        $this->assertStringContainsString('Anniversary', $rendered);
        $this->assertStringContainsString('14-Feb-2015', $rendered);
    }

    /** @test */
    public function it_sends_engagement_anniversary_notification()
    {
        $this->createAnniversaryTemplate('engagement');

        $customer = Customer::factory()->create([
            'name' => 'Engaged Person',
            'engagement_anniversary_date' => '2014-12-25',
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromCustomer('engagement_anniversary', 'whatsapp', $customer);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('Engaged Person', $rendered);
        $this->assertStringContainsString('25-Dec-2014', $rendered);
    }

    // =======================================================
    // FALLBACK & ERROR HANDLING TESTS
    // =======================================================

    /** @test */
    public function it_returns_null_when_template_is_missing()
    {
        $customer = Customer::factory()->create();

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromCustomer('non_existent_type', 'whatsapp', $customer);

        $this->assertNull($rendered);
    }

    /** @test */
    public function it_handles_customers_without_optional_fields()
    {
        $this->createWelcomeTemplate();

        $customer = Customer::factory()->create([
            'name' => 'Minimal User',
            'mobile_number' => '9999999999',
            'date_of_birth' => null,
            'wedding_anniversary_date' => null,
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromCustomer('customer_welcome', 'whatsapp', $customer);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('Minimal User', $rendered);
    }

    // =======================================================
    // MULTI-CHANNEL TESTS
    // =======================================================

    /** @test */
    public function it_sends_welcome_via_whatsapp_channel()
    {
        $this->createWelcomeTemplate('whatsapp');

        $customer = Customer::factory()->create(['name' => 'WhatsApp User']);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromCustomer('customer_welcome', 'whatsapp', $customer);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('WhatsApp User', $rendered);
    }

    /** @test */
    public function it_sends_welcome_via_email_channel()
    {
        $this->createWelcomeTemplate('email');

        $customer = Customer::factory()->create(['name' => 'Email User', 'email' => 'email@test.com']);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromCustomer('customer_welcome', 'email', $customer);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('Email User', $rendered);
    }

    /** @test */
    public function it_returns_null_for_inactive_channel()
    {
        // Create only WhatsApp template
        $this->createWelcomeTemplate('whatsapp');

        $customer = Customer::factory()->create();

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromCustomer('customer_welcome', 'email', $customer);

        $this->assertNull($rendered);
    }

    // =======================================================
    // HELPER METHODS
    // =======================================================

    protected function createAppSettings()
    {
        AppSetting::factory()->create([
            'category' => 'company',
            'key' => 'name',
            'value' => 'Midas Insurance',
            'is_active' => true,
        ]);

        AppSetting::factory()->create([
            'category' => 'company',
            'key' => 'advisor_name',
            'value' => 'Your Trusted Advisor',
            'is_active' => true,
        ]);

        AppSetting::factory()->create([
            'category' => 'company',
            'key' => 'phone',
            'value' => '+91 98765 43210',
            'is_active' => true,
        ]);

        AppSetting::factory()->create([
            'category' => 'company',
            'key' => 'website',
            'value' => 'https://midas.com',
            'is_active' => true,
        ]);
    }

    protected function createWelcomeTemplate($channel = 'whatsapp')
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'customer_welcome',
            'name' => 'Customer Welcome',
            'category' => 'customer',
            'is_active' => true,
        ]);

        NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => $channel,
            'template_content' => 'Welcome {{customer_name}}! Thank you for choosing {{company_name}}. Contact us at {{company_phone}}.',
            'is_active' => true,
        ]);
    }

    protected function createBirthdayTemplate()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'birthday_wish',
            'name' => 'Birthday Wish',
            'category' => 'customer',
            'is_active' => true,
        ]);

        NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Happy Birthday {{customer_name}}! Born on {{date_of_birth}}. Best wishes from {{company_name}}.',
            'is_active' => true,
        ]);
    }

    protected function createAnniversaryTemplate($type = 'wedding')
    {
        $code = $type.'_anniversary';
        $variable = $type.'_anniversary';

        $notificationType = NotificationType::factory()->create([
            'code' => $code,
            'name' => ucfirst($type).' Anniversary',
            'category' => 'customer',
            'is_active' => true,
        ]);

        NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Happy Anniversary {{customer_name}}! Celebrating since {{'.$variable.'}}. From {{company_name}}.',
            'is_active' => true,
        ]);
    }
}
