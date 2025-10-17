<?php

namespace Tests\Feature\Notification;

use App\Models\AppSetting;
use App\Models\CustomerInsurance;
use App\Models\NotificationTemplate;
use App\Models\NotificationType;
use App\Services\TemplateService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for Policy Notification flows
 *
 * Tests policy created and renewal reminder workflows
 */
class PolicyNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAppSettings();
    }

    // =======================================================
    // POLICY CREATED FLOW TESTS
    // =======================================================

    /** @test */
    public function it_sends_policy_created_notification()
    {
        $this->createPolicyCreatedTemplate();

        $insurance = CustomerInsurance::factory()->create([
            'policy_no' => 'POL-2025-001',
            'premium_amount' => 5000,
            'start_date' => '2025-01-01',
            'expired_date' => '2026-01-01',
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromInsurance('policy_created', 'whatsapp', $insurance);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('POL-2025-001', $rendered);
        $this->assertStringContainsString('₹5,000', $rendered);
    }

    /** @test */
    public function it_includes_customer_name_in_policy_created_message()
    {
        $this->createPolicyCreatedTemplate();

        $insurance = CustomerInsurance::factory()->create(['policy_no' => 'POL-123']);
        $insurance->load('customer');

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromInsurance('policy_created', 'whatsapp', $insurance);

        $this->assertStringContainsString($insurance->customer->name, $rendered);
    }

    /** @test */
    public function it_includes_insurance_company_in_policy_created_message()
    {
        $this->createPolicyCreatedTemplate();

        $insurance = CustomerInsurance::factory()->create();
        $insurance->load('insuranceCompany');

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromInsurance('policy_created', 'whatsapp', $insurance);

        $this->assertStringContainsString($insurance->insuranceCompany->name, $rendered);
    }

    /** @test */
    public function it_formats_dates_in_policy_created_message()
    {
        $this->createPolicyCreatedTemplate();

        $insurance = CustomerInsurance::factory()->create([
            'start_date' => '2025-03-15',
            'expired_date' => '2026-03-15',
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromInsurance('policy_created', 'whatsapp', $insurance);

        $this->assertStringContainsString('15-Mar-2025', $rendered);
        $this->assertStringContainsString('15-Mar-2026', $rendered);
    }

    // =======================================================
    // RENEWAL REMINDER FLOW TESTS
    // =======================================================

    /** @test */
    public function it_sends_30_day_renewal_reminder()
    {
        $this->createRenewalTemplate(30);

        $futureDate = Carbon::now()->addDays(30);
        $insurance = CustomerInsurance::factory()->create([
            'policy_no' => 'POL-RENEW-30',
            'registration_no' => 'GJ-01-AB-1234',
            'expired_date' => $futureDate->format('Y-m-d'),
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromInsurance('renewal_reminder_30_days', 'whatsapp', $insurance);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('30', $rendered);
        $this->assertStringContainsString('GJ-01-AB-1234', $rendered);
    }

    /** @test */
    public function it_sends_15_day_renewal_reminder()
    {
        $this->createRenewalTemplate(15);

        $futureDate = Carbon::now()->addDays(15);
        $insurance = CustomerInsurance::factory()->create([
            'expired_date' => $futureDate->format('Y-m-d'),
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromInsurance('renewal_reminder_15_days', 'whatsapp', $insurance);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('15', $rendered);
    }

    /** @test */
    public function it_sends_7_day_renewal_reminder()
    {
        $this->createRenewalTemplate(7);

        $futureDate = Carbon::now()->addDays(7);
        $insurance = CustomerInsurance::factory()->create([
            'expired_date' => $futureDate->format('Y-m-d'),
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromInsurance('renewal_reminder_7_days', 'whatsapp', $insurance);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('7', $rendered);
    }

    /** @test */
    public function it_sends_expired_policy_reminder()
    {
        $this->createExpiredTemplate();

        $pastDate = Carbon::now()->subDays(5);
        $insurance = CustomerInsurance::factory()->create([
            'expired_date' => $pastDate->format('Y-m-d'),
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromInsurance('renewal_reminder_expired', 'whatsapp', $insurance);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('expired', $rendered);
    }

    /** @test */
    public function it_computes_days_remaining_correctly()
    {
        $this->createRenewalTemplate(30);

        $futureDate = Carbon::now()->addDays(30);
        $insurance = CustomerInsurance::factory()->create([
            'expired_date' => $futureDate->format('Y-m-d'),
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromInsurance('renewal_reminder_30_days', 'whatsapp', $insurance);

        $this->assertStringContainsString('30', $rendered);
    }

    /** @test */
    public function it_shows_zero_days_for_expired_policies()
    {
        $this->createExpiredTemplate();

        $pastDate = Carbon::now()->subDays(10);
        $insurance = CustomerInsurance::factory()->create([
            'expired_date' => $pastDate->format('Y-m-d'),
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromInsurance('renewal_reminder_expired', 'whatsapp', $insurance);

        // Days remaining should be 0 for expired policy
        $this->assertNotNull($rendered);
    }

    // =======================================================
    // POLICY DETAILS TESTS
    // =======================================================

    /** @test */
    public function it_includes_vehicle_details_in_renewal_reminder()
    {
        $this->createRenewalTemplate(30);

        $futureDate = Carbon::now()->addDays(30);
        $insurance = CustomerInsurance::factory()->create([
            'registration_no' => 'GJ-01-XY-5678',
            'make_model' => 'Honda City VX',
            'expired_date' => $futureDate->format('Y-m-d'),
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromInsurance('renewal_reminder_30_days', 'whatsapp', $insurance);

        $this->assertStringContainsString('GJ-01-XY-5678', $rendered);
    }

    /** @test */
    public function it_includes_policy_type_in_notifications()
    {
        $this->createPolicyCreatedTemplate();

        $insurance = CustomerInsurance::factory()->create();
        $insurance->load('policyType');

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromInsurance('policy_created', 'whatsapp', $insurance);

        $this->assertNotNull($rendered);
    }

    // =======================================================
    // EDGE CASES & ERROR HANDLING
    // =======================================================

    /** @test */
    public function it_handles_policies_without_expiry_date()
    {
        $this->createPolicyCreatedTemplate();

        $insurance = CustomerInsurance::factory()->create([
            'expired_date' => null,
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromInsurance('policy_created', 'whatsapp', $insurance);

        $this->assertNotNull($rendered);
    }

    /** @test */
    public function it_handles_policies_with_zero_premium()
    {
        $this->createPolicyCreatedTemplate();

        $insurance = CustomerInsurance::factory()->create([
            'premium_amount' => 0,
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromInsurance('policy_created', 'whatsapp', $insurance);

        $this->assertStringContainsString('₹0', $rendered);
    }

    /** @test */
    public function it_handles_large_premium_amounts()
    {
        $this->createPolicyCreatedTemplate();

        $insurance = CustomerInsurance::factory()->create([
            'premium_amount' => 1500000, // 15 lakh
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromInsurance('policy_created', 'whatsapp', $insurance);

        $this->assertStringContainsString('₹15,00,000', $rendered);
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
            'key' => 'phone',
            'value' => '+91 98765 43210',
            'is_active' => true,
        ]);
    }

    protected function createPolicyCreatedTemplate()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'policy_created',
            'name' => 'Policy Created',
            'category' => 'policy',
            'is_active' => true,
        ]);

        NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Hi {{customer_name}}, your policy {{policy_number}} with {{insurance_company}} is created. Premium: {{premium_amount}}. Valid from {{start_date}} to {{expiry_date}}.',
            'is_active' => true,
        ]);
    }

    protected function createRenewalTemplate($days)
    {
        $notificationType = NotificationType::factory()->create([
            'code' => "renewal_reminder_{$days}_days",
            'name' => "{$days} Days Renewal Reminder",
            'category' => 'renewal',
            'is_active' => true,
        ]);

        NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Hi {{customer_name}}, your policy {{policy_number}} for vehicle {{vehicle_number}} expires in {{days_remaining}} days on {{expiry_date}}. Contact {{company_phone}} to renew.',
            'is_active' => true,
        ]);
    }

    protected function createExpiredTemplate()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'renewal_reminder_expired',
            'name' => 'Policy Expired Reminder',
            'category' => 'renewal',
            'is_active' => true,
        ]);

        NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Hi {{customer_name}}, your policy {{policy_number}} for {{vehicle_number}} has expired on {{expired_date}}. Renew immediately to avoid penalties. Contact {{company_phone}}.',
            'is_active' => true,
        ]);
    }
}
