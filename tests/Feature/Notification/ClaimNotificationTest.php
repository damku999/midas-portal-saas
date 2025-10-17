<?php

namespace Tests\Feature\Notification;

use App\Models\AppSetting;
use App\Models\Claim;
use App\Models\ClaimDocument;
use App\Models\NotificationTemplate;
use App\Models\NotificationType;
use App\Services\TemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for Claim Notification flows
 *
 * Tests claim initiated and document list workflows (dynamic)
 */
class ClaimNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAppSettings();
    }

    // =======================================================
    // CLAIM INITIATED FLOW TESTS
    // =======================================================

    /** @test */
    public function it_sends_claim_initiated_notification()
    {
        $this->createClaimInitiatedTemplate();

        $claim = Claim::factory()->create(['claim_number' => 'CLM-2025-001']);
        $claim->load(['insurance.customer', 'insurance.insuranceCompany']);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromClaim('claim_initiated', 'whatsapp', $claim);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('CLM-2025-001', $rendered);
    }

    /** @test */
    public function it_includes_customer_name_in_claim_notification()
    {
        $this->createClaimInitiatedTemplate();

        $claim = Claim::factory()->create();
        $claim->load(['insurance.customer']);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromClaim('claim_initiated', 'whatsapp', $claim);

        $this->assertStringContainsString($claim->customer->name, $rendered);
    }

    /** @test */
    public function it_includes_policy_number_in_claim_notification()
    {
        $this->createClaimInitiatedTemplate();

        $claim = Claim::factory()->create();
        $claim->load(['insurance']);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromClaim('claim_initiated', 'whatsapp', $claim);

        $this->assertStringContainsString($claim->insurance->policy_no, $rendered);
    }

    // =======================================================
    // CLAIM STAGE UPDATE FLOW TESTS
    // =======================================================

    /** @test */
    public function it_sends_claim_stage_update_notification()
    {
        $this->createClaimStageUpdateTemplate();

        $claim = Claim::factory()->create([
            'claim_number' => 'CLM-2025-002',
            'status' => 'In Progress',
        ]);
        $claim->load(['insurance.customer']);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromClaim('claim_stage_update', 'whatsapp', $claim);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('CLM-2025-002', $rendered);
    }

    // =======================================================
    // PENDING DOCUMENTS LIST TESTS (DYNAMIC)
    // =======================================================

    /** @test */
    public function it_generates_pending_documents_list_dynamically()
    {
        $this->createClaimStageUpdateTemplate();

        $claim = Claim::factory()->create();

        // Create pending documents
        ClaimDocument::factory()->create([
            'claim_id' => $claim->id,
            'document_name' => 'Vehicle RC Copy',
            'is_submitted' => false,
        ]);

        ClaimDocument::factory()->create([
            'claim_id' => $claim->id,
            'document_name' => 'Police FIR',
            'is_submitted' => false,
        ]);

        ClaimDocument::factory()->create([
            'claim_id' => $claim->id,
            'document_name' => 'Driving License',
            'is_submitted' => false,
        ]);

        $claim->load(['insurance.customer', 'documents']);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromClaim('claim_stage_update', 'whatsapp', $claim);

        $this->assertStringContainsString('1. Vehicle RC Copy', $rendered);
        $this->assertStringContainsString('2. Police FIR', $rendered);
        $this->assertStringContainsString('3. Driving License', $rendered);
    }

    /** @test */
    public function it_shows_numbered_list_for_pending_documents()
    {
        $this->createClaimStageUpdateTemplate();

        $claim = Claim::factory()->create();

        ClaimDocument::factory()->count(5)->create([
            'claim_id' => $claim->id,
            'is_submitted' => false,
        ]);

        $claim->load(['insurance.customer', 'documents']);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromClaim('claim_stage_update', 'whatsapp', $claim);

        $this->assertStringContainsString('1.', $rendered);
        $this->assertStringContainsString('2.', $rendered);
        $this->assertStringContainsString('3.', $rendered);
        $this->assertStringContainsString('4.', $rendered);
        $this->assertStringContainsString('5.', $rendered);
    }

    /** @test */
    public function it_excludes_submitted_documents_from_pending_list()
    {
        $this->createClaimStageUpdateTemplate();

        $claim = Claim::factory()->create();

        ClaimDocument::factory()->create([
            'claim_id' => $claim->id,
            'document_name' => 'RC Copy',
            'is_submitted' => false, // Pending
        ]);

        ClaimDocument::factory()->create([
            'claim_id' => $claim->id,
            'document_name' => 'Insurance Policy',
            'is_submitted' => true, // Already submitted
        ]);

        $claim->load(['insurance.customer', 'documents']);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromClaim('claim_stage_update', 'whatsapp', $claim);

        $this->assertStringContainsString('RC Copy', $rendered);
        $this->assertStringNotContainsString('Insurance Policy', $rendered);
    }

    /** @test */
    public function it_shows_no_pending_documents_message_when_all_submitted()
    {
        $this->createClaimStageUpdateTemplate();

        $claim = Claim::factory()->create();

        // All documents submitted
        ClaimDocument::factory()->count(3)->create([
            'claim_id' => $claim->id,
            'is_submitted' => true,
        ]);

        $claim->load(['insurance.customer', 'documents']);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromClaim('claim_stage_update', 'whatsapp', $claim);

        $this->assertStringContainsString('No pending documents', $rendered);
    }

    /** @test */
    public function it_handles_claim_with_no_documents()
    {
        $this->createClaimStageUpdateTemplate();

        $claim = Claim::factory()->create();
        $claim->load(['insurance.customer', 'documents']);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromClaim('claim_stage_update', 'whatsapp', $claim);

        $this->assertNotNull($rendered);
        $this->assertStringContainsString('No pending documents', $rendered);
    }

    /** @test */
    public function it_handles_many_pending_documents()
    {
        $this->createClaimStageUpdateTemplate();

        $claim = Claim::factory()->create();

        // Create 10 pending documents
        for ($i = 1; $i <= 10; $i++) {
            ClaimDocument::factory()->create([
                'claim_id' => $claim->id,
                'document_name' => "Document $i",
                'is_submitted' => false,
            ]);
        }

        $claim->load(['insurance.customer', 'documents']);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromClaim('claim_stage_update', 'whatsapp', $claim);

        $this->assertStringContainsString('1. Document 1', $rendered);
        $this->assertStringContainsString('10. Document 10', $rendered);
    }

    // =======================================================
    // EDGE CASES & ERROR HANDLING
    // =======================================================

    /** @test */
    public function it_handles_claim_without_insurance()
    {
        $this->createClaimInitiatedTemplate();

        $claim = Claim::factory()->create();

        // Try to render without proper relationships loaded
        $templateService = app(TemplateService::class);

        // Should handle gracefully
        $result = $templateService->renderFromClaim('claim_initiated', 'whatsapp', $claim);

        $this->assertNotNull($result);
    }

    /** @test */
    public function it_handles_special_characters_in_document_names()
    {
        $this->createClaimStageUpdateTemplate();

        $claim = Claim::factory()->create();

        ClaimDocument::factory()->create([
            'claim_id' => $claim->id,
            'document_name' => 'RC & Insurance Copy (Original)',
            'is_submitted' => false,
        ]);

        $claim->load(['insurance.customer', 'documents']);

        $templateService = app(TemplateService::class);
        $rendered = $templateService->renderFromClaim('claim_stage_update', 'whatsapp', $claim);

        $this->assertStringContainsString('RC & Insurance Copy (Original)', $rendered);
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

    protected function createClaimInitiatedTemplate()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'claim_initiated',
            'name' => 'Claim Initiated',
            'category' => 'claim',
            'is_active' => true,
        ]);

        NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Hi {{customer_name}}, your claim {{claim_number}} for policy {{policy_number}} has been initiated. Our team will contact you shortly. Call {{company_phone}} for assistance.',
            'is_active' => true,
        ]);
    }

    protected function createClaimStageUpdateTemplate()
    {
        $notificationType = NotificationType::factory()->create([
            'code' => 'claim_stage_update',
            'name' => 'Claim Stage Update',
            'category' => 'claim',
            'is_active' => true,
        ]);

        NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => 'whatsapp',
            'template_content' => 'Hi {{customer_name}}, update on claim {{claim_number}}:

Pending Documents:
{{pending_documents_list}}

Please submit these documents at the earliest. Contact {{company_phone}}.',
            'is_active' => true,
        ]);
    }
}
