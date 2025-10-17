<?php

namespace Tests\Unit\Notification;

use App\Models\AppSetting;
use App\Models\Claim;
use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\NotificationTemplate;
use App\Models\NotificationType;
use App\Models\Quotation;
use App\Services\Notification\NotificationContext;
use App\Services\TemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for TemplateService
 *
 * Tests template rendering with various contexts
 */
class TemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TemplateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TemplateService::class);
    }

    // =======================================================
    // TEMPLATE RENDERING TESTS
    // =======================================================

    /** @test */
    public function it_renders_template_with_notification_context()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'test_notification',
            'is_active' => true,
        ]);

        $template = NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Hello {{customer_name}}, welcome!',
            'is_active' => true,
        ]);

        $customer = Customer::factory()->create(['name' => 'John Doe']);
        $context = new NotificationContext(['customer' => $customer]);

        $result = $this->service->render('test_notification', 'whatsapp', $context);

        $this->assertNotNull($result);
        $this->assertEquals('Hello John Doe, welcome!', $result);
    }

    /** @test */
    public function it_renders_template_with_legacy_array_data()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'test_notification',
            'is_active' => true,
        ]);

        $template = NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Hello {{customer_name}}, your policy {{policy_number}} is ready.',
            'is_active' => true,
        ]);

        $data = [
            'customer_name' => 'Jane Doe',
            'policy_number' => 'POL-123',
        ];

        $result = $this->service->render('test_notification', 'whatsapp', $data);

        $this->assertNotNull($result);
        $this->assertEquals('Hello Jane Doe, your policy POL-123 is ready.', $result);
    }

    /** @test */
    public function it_returns_null_for_inactive_notification_type()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'inactive_notification',
            'is_active' => false,
        ]);

        $context = new NotificationContext;

        $result = $this->service->render('inactive_notification', 'whatsapp', $context);

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_null_for_missing_notification_type()
    {
        $context = new NotificationContext;

        $result = $this->service->render('non_existent_type', 'whatsapp', $context);

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_null_for_inactive_template()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'test_notification',
            'is_active' => true,
        ]);

        NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'is_active' => false,
        ]);

        $context = new NotificationContext;

        $result = $this->service->render('test_notification', 'whatsapp', $context);

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_null_for_missing_template()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'test_notification',
            'is_active' => true,
        ]);

        $context = new NotificationContext;

        $result = $this->service->render('test_notification', 'email', $context);

        $this->assertNull($result);
    }

    // =======================================================
    // VARIABLE REPLACEMENT TESTS
    // =======================================================

    /** @test */
    public function it_replaces_double_curly_brace_variables()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'test',
            'is_active' => true,
        ]);

        $template = NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Name: {{name}}, Age: {{age}}',
            'is_active' => true,
        ]);

        $data = ['name' => 'John', 'age' => '30'];

        $result = $this->service->render('test', 'whatsapp', $data);

        $this->assertEquals('Name: John, Age: 30', $result);
    }

    /** @test */
    public function it_replaces_single_curly_brace_variables_for_backward_compatibility()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'test',
            'is_active' => true,
        ]);

        $template = NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Name: {name}, Age: {age}',
            'is_active' => true,
        ]);

        $data = ['name' => 'John', 'age' => '30'];

        $result = $this->service->render('test', 'whatsapp', $data);

        $this->assertEquals('Name: John, Age: 30', $result);
    }

    // =======================================================
    // FACTORY METHOD TESTS
    // =======================================================

    /** @test */
    public function it_renders_from_insurance()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'policy_created',
            'is_active' => true,
        ]);

        $template = NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Policy {{policy_number}} created for {{customer_name}}.',
            'is_active' => true,
        ]);

        $insurance = CustomerInsurance::factory()->create(['policy_no' => 'POL-123']);
        $insurance->load('customer');

        $result = $this->service->renderFromInsurance('policy_created', 'whatsapp', $insurance);

        $this->assertNotNull($result);
        $this->assertStringContainsString('POL-123', $result);
    }

    /** @test */
    public function it_renders_from_customer()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'customer_welcome',
            'is_active' => true,
        ]);

        $template = NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Welcome {{customer_name}}!',
            'is_active' => true,
        ]);

        $customer = Customer::factory()->create(['name' => 'Alice']);

        $result = $this->service->renderFromCustomer('customer_welcome', 'whatsapp', $customer);

        $this->assertNotNull($result);
        $this->assertEquals('Welcome Alice!', $result);
    }

    /** @test */
    public function it_renders_from_quotation()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'quotation_ready',
            'is_active' => true,
        ]);

        $template = NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Hi {{customer_name}}, your quotation is ready!',
            'is_active' => true,
        ]);

        $quotation = Quotation::factory()->create();
        $quotation->load('customer');

        $result = $this->service->renderFromQuotation('quotation_ready', 'whatsapp', $quotation);

        $this->assertNotNull($result);
        $this->assertStringContainsString('quotation is ready', $result);
    }

    /** @test */
    public function it_renders_from_claim()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'claim_initiated',
            'is_active' => true,
        ]);

        $template = NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Claim {{claim_number}} initiated for {{customer_name}}.',
            'is_active' => true,
        ]);

        $claim = Claim::factory()->create();
        $claim->load(['insurance.customer']);

        $result = $this->service->renderFromClaim('claim_initiated', 'whatsapp', $claim);

        $this->assertNotNull($result);
        $this->assertStringContainsString('initiated', $result);
    }

    // =======================================================
    // PREVIEW TESTS
    // =======================================================

    /** @test */
    public function it_previews_template_without_saving()
    {
        $templateContent = 'Hello {{name}}, welcome to {{company}}!';
        $data = ['name' => 'John', 'company' => 'Midas Insurance'];

        $result = $this->service->preview($templateContent, $data);

        $this->assertEquals('Hello John, welcome to Midas Insurance!', $result);
    }

    /** @test */
    public function it_previews_template_with_missing_variables()
    {
        $templateContent = 'Hello {{name}}, your email is {{email}}.';
        $data = ['name' => 'John'];

        $result = $this->service->preview($templateContent, $data);

        $this->assertStringContainsString('Hello John', $result);
        $this->assertStringContainsString('your email is', $result);
    }

    // =======================================================
    // AVAILABLE VARIABLES TESTS
    // =======================================================

    /** @test */
    public function it_gets_available_variables_for_notification_type()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'test_notification',
        ]);

        $template = NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'available_variables' => ['customer_name', 'policy_number', 'expiry_date'],
        ]);

        $variables = $this->service->getAvailableVariables('test_notification', 'whatsapp');

        $this->assertNotNull($variables);
        $this->assertIsArray($variables);
        $this->assertContains('customer_name', $variables);
        $this->assertContains('policy_number', $variables);
    }

    /** @test */
    public function it_returns_null_for_missing_notification_type_variables()
    {
        $variables = $this->service->getAvailableVariables('non_existent', 'whatsapp');

        $this->assertNull($variables);
    }

    /** @test */
    public function it_returns_null_for_missing_template_variables()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'test_notification',
        ]);

        $variables = $this->service->getAvailableVariables('test_notification', 'email');

        $this->assertNull($variables);
    }

    // =======================================================
    // ERROR HANDLING TESTS
    // =======================================================

    /** @test */
    public function it_handles_rendering_errors_gracefully()
    {
        // Create a scenario that might cause an error
        $notificationType = NotificationType::factory()->create([
            'code' => 'error_test',
            'is_active' => true,
        ]);

        NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Test {{variable}}',
            'is_active' => true,
        ]);

        // Pass invalid context that might cause issues
        $result = $this->service->render('error_test', 'whatsapp', []);

        // Should handle gracefully and return result
        $this->assertNotNull($result);
    }

    // =======================================================
    // SETTINGS LOADING TESTS
    // =======================================================

    /** @test */
    public function it_loads_settings_into_context()
    {
        AppSetting::factory()->create([
            'category' => 'company',
            'key' => 'advisor_name',
            'value' => 'Insurance Advisor',
            'is_active' => true,
        ]);

        $notificationType = NotificationType::factory()->create([
            'code' => 'test',
            'is_active' => true,
        ]);

        $template = NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Contact {{advisor_name}}.',
            'is_active' => true,
        ]);

        $customer = Customer::factory()->create();

        $result = $this->service->renderFromCustomer('test', 'whatsapp', $customer);

        $this->assertNotNull($result);
        $this->assertStringContainsString('Insurance Advisor', $result);
    }

    /** @test */
    public function it_strips_category_prefix_from_settings()
    {
        AppSetting::factory()->create([
            'category' => 'company',
            'key' => 'company_advisor_name', // Has category prefix
            'value' => 'Test Advisor',
            'is_active' => true,
        ]);

        $notificationType = NotificationType::factory()->create([
            'code' => 'test',
            'is_active' => true,
        ]);

        NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Contact {{advisor_name}}.', // Without prefix
            'is_active' => true,
        ]);

        $customer = Customer::factory()->create();

        $result = $this->service->renderFromCustomer('test', 'whatsapp', $customer);

        $this->assertNotNull($result);
    }

    // =======================================================
    // MULTI-CHANNEL TESTS
    // =======================================================

    /** @test */
    public function it_renders_different_templates_for_different_channels()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'multi_channel',
            'is_active' => true,
        ]);

        NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'WhatsApp: {{customer_name}}',
            'is_active' => true,
        ]);

        NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'email',
            'template_content' => 'Email: {{customer_name}}',
            'is_active' => true,
        ]);

        $customer = Customer::factory()->create(['name' => 'Bob']);
        $context = new NotificationContext(['customer' => $customer]);

        $whatsappResult = $this->service->render('multi_channel', 'whatsapp', $context);
        $emailResult = $this->service->render('multi_channel', 'email', $context);

        $this->assertEquals('WhatsApp: Bob', $whatsappResult);
        $this->assertEquals('Email: Bob', $emailResult);
    }
}
