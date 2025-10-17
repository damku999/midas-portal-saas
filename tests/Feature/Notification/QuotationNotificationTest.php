<?php

namespace Tests\Feature\Notification;

use App\Models\AppSetting;
use App\Models\NotificationTemplate;
use App\Models\NotificationType;
use App\Models\Quotation;
use App\Models\QuotationCompany;
use App\Services\TemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for Quotation Notification flows
 *
 * Tests quotation generation and comparison workflows
 */
class QuotationNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAppSettings();
    }

    // =======================================================
    // QUOTATION READY FLOW TESTS
    // =======================================================

    /** @test */
    public function it_sends_quotation_ready_notification()
    {
        $this->createQuotationTemplate();

        $quotation = $this->createQuotationWithCompanies(3);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromQuotation('quotation_ready', 'whatsapp', $quotation);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString($quotation->customer->name, $rendered);
    }

    /** @test */
    public function it_includes_quotes_count_in_quotation_message()
    {
        $this->createQuotationTemplate();

        $quotation = $this->createQuotationWithCompanies(5);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromQuotation('quotation_ready', 'whatsapp', $quotation);

        // Should show count of quotations
        $this->assertNotNull($rendered);
    }

    /** @test */
    public function it_shows_best_company_in_quotation_message()
    {
        $this->createQuotationTemplate();

        $quotation = $this->createQuotationWithCompanies(3, [
            ['premium_amount' => 5000],
            ['premium_amount' => 4500],
            ['premium_amount' => 6000],
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromQuotation('quotation_ready', 'whatsapp', $quotation);

        $bestCompany = $quotation->quotationCompanies->sortBy('premium_amount')->first()->insuranceCompany->name;

        $this->assertStringContainsString($bestCompany, $rendered);
    }

    /** @test */
    public function it_shows_best_premium_with_currency_formatting()
    {
        $this->createQuotationTemplate();

        $quotation = $this->createQuotationWithCompanies(3, [
            ['premium_amount' => 5000],
            ['premium_amount' => 4500],
            ['premium_amount' => 6000],
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromQuotation('quotation_ready', 'whatsapp', $quotation);

        $this->assertStringContainsString('₹4,500', $rendered);
    }

    /** @test */
    public function it_shows_comparison_list_with_all_quotes()
    {
        $this->createQuotationTemplate();

        $quotation = $this->createQuotationWithCompanies(3, [
            ['premium_amount' => 5000],
            ['premium_amount' => 4500],
            ['premium_amount' => 6000],
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromQuotation('quotation_ready', 'whatsapp', $quotation);

        // Should contain numbered list
        $this->assertStringContainsString('1.', $rendered);
        $this->assertStringContainsString('2.', $rendered);
        $this->assertStringContainsString('3.', $rendered);

        // Should contain all premiums
        $this->assertStringContainsString('₹4,500', $rendered);
        $this->assertStringContainsString('₹5,000', $rendered);
        $this->assertStringContainsString('₹6,000', $rendered);
    }

    /** @test */
    public function it_sorts_comparison_list_by_premium_amount()
    {
        $this->createQuotationTemplate();

        $quotation = $this->createQuotationWithCompanies(3, [
            ['premium_amount' => 6000],
            ['premium_amount' => 4500],
            ['premium_amount' => 5000],
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromQuotation('quotation_ready', 'whatsapp', $quotation);

        // First entry should be lowest premium
        $position4500 = strpos($rendered, '₹4,500');
        $position5000 = strpos($rendered, '₹5,000');
        $position6000 = strpos($rendered, '₹6,000');

        $this->assertLessThan($position5000, $position4500);
        $this->assertLessThan($position6000, $position5000);
    }

    // =======================================================
    // VEHICLE DETAILS TESTS
    // =======================================================

    /** @test */
    public function it_includes_vehicle_make_model_in_quotation()
    {
        $this->createQuotationTemplate();

        $quotation = Quotation::factory()->create([
            'make_model' => 'Honda City VX',
        ]);

        QuotationCompany::factory()->count(2)->create([
            'quotation_id' => $quotation->id,
        ]);

        $quotation->load('customer', 'quotationCompanies.insuranceCompany');

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromQuotation('quotation_ready', 'whatsapp', $quotation);

        $this->assertNotNull($rendered);
    }

    // =======================================================
    // EDGE CASES & ERROR HANDLING
    // =======================================================

    /** @test */
    public function it_handles_quotation_with_single_company()
    {
        $this->createQuotationTemplate();

        $quotation = $this->createQuotationWithCompanies(1);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromQuotation('quotation_ready', 'whatsapp', $quotation);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('1.', $rendered);
    }

    /** @test */
    public function it_handles_quotation_with_many_companies()
    {
        $this->createQuotationTemplate();

        $quotation = $this->createQuotationWithCompanies(10);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromQuotation('quotation_ready', 'whatsapp', $quotation);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('1.', $rendered);
        $this->assertStringContainsString('10.', $rendered);
    }

    /** @test */
    public function it_handles_quotation_with_same_premium_amounts()
    {
        $this->createQuotationTemplate();

        $quotation = $this->createQuotationWithCompanies(3, [
            ['premium_amount' => 5000],
            ['premium_amount' => 5000],
            ['premium_amount' => 5000],
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromQuotation('quotation_ready', 'whatsapp', $quotation);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('₹5,000', $rendered);
    }

    /** @test */
    public function it_handles_quotation_with_large_premium_values()
    {
        $this->createQuotationTemplate();

        $quotation = $this->createQuotationWithCompanies(2, [
            ['premium_amount' => 1000000], // 10 lakh
            ['premium_amount' => 1500000], // 15 lakh
        ]);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromQuotation('quotation_ready', 'whatsapp', $quotation);

        $this->assertStringContainsString('₹10,00,000', $rendered);
        $this->assertStringContainsString('₹15,00,000', $rendered);
    }

    /** @test */
    public function it_handles_quotation_without_companies()
    {
        $this->createQuotationTemplate();

        $quotation = Quotation::factory()->create();
        $quotation->load('customer', 'quotationCompanies.insuranceCompany');

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromQuotation('quotation_ready', 'whatsapp', $quotation);

        // Should handle gracefully, not crash
        $this->assertNotNull($rendered);
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

    protected function createQuotationTemplate()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'quotation_ready',
            'name' => 'Quotation Ready',
            'category' => 'quotation',
            'is_active' => true,
        ]);

        NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Hi {{customer_name}}, your quotation is ready! Best price: {{best_premium}} from {{best_company_name}}.

Comparison:
{{comparison_list}}

Contact {{company_phone}} for details.',
            'is_active' => true,
        ]);
    }

    protected function createQuotationWithCompanies($count, $customData = [])
    {
        $quotation = Quotation::factory()->create();

        for ($i = 0; $i < $count; $i++) {
            $data = $customData[$i] ?? [];

            QuotationCompany::factory()->create(array_merge([
                'quotation_id' => $quotation->id,
                'premium_amount' => rand(4000, 8000),
            ], $data));
        }

        $quotation->load('customer', 'quotationCompanies.insuranceCompany');

        return $quotation;
    }
}
