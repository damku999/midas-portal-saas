<?php

namespace Tests\Unit\Notification;

use App\Services\Notification\VariableRegistryService;
use Tests\TestCase;

/**
 * Test suite for VariableRegistryService
 *
 * Tests variable metadata and extraction functionality
 */
class VariableRegistryServiceTest extends TestCase
{
    protected VariableRegistryService $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new VariableRegistryService;
    }

    // =======================================================
    // VARIABLE RETRIEVAL TESTS
    // =======================================================

    /** @test */
    public function it_loads_all_variables_from_config()
    {
        $variables = $this->registry->getAllVariables();

        $this->assertGreaterThan(50, $variables->count());
    }

    /** @test */
    public function it_loads_all_categories_from_config()
    {
        $categories = $this->registry->getAllCategories();

        $this->assertGreaterThan(5, $categories->count());
        $this->assertTrue($categories->contains('key', 'customer'));
        $this->assertTrue($categories->contains('key', 'policy'));
    }

    /** @test */
    public function it_gets_variable_metadata_by_key()
    {
        $metadata = $this->registry->getVariableMetadata('customer_name');

        $this->assertNotNull($metadata);
        $this->assertEquals('customer_name', $metadata['key']);
        $this->assertEquals('customer', $metadata['category']);
        $this->assertArrayHasKey('source', $metadata);
        $this->assertArrayHasKey('label', $metadata);
    }

    /** @test */
    public function it_returns_null_for_unknown_variable()
    {
        $metadata = $this->registry->getVariableMetadata('unknown_var');

        $this->assertNull($metadata);
    }

    /** @test */
    public function it_checks_if_variable_exists()
    {
        $this->assertTrue($this->registry->hasVariable('customer_name'));
        $this->assertFalse($this->registry->hasVariable('unknown_var'));
    }

    // =======================================================
    // CATEGORY FILTERING TESTS
    // =======================================================

    /** @test */
    public function it_gets_variables_by_category()
    {
        $customerVars = $this->registry->getVariablesByCategory('customer');

        $this->assertGreaterThan(0, $customerVars->count());
        $this->assertTrue($customerVars->contains('key', 'customer_name'));
        $this->assertTrue($customerVars->contains('key', 'customer_email'));
    }

    /** @test */
    public function it_gets_policy_variables_by_category()
    {
        $policyVars = $this->registry->getVariablesByCategory('policy');

        $this->assertGreaterThan(0, $policyVars->count());
        $this->assertTrue($policyVars->contains('key', 'policy_number'));
        $this->assertTrue($policyVars->contains('key', 'premium_amount'));
    }

    /** @test */
    public function it_gets_variables_grouped_by_category()
    {
        $grouped = $this->registry->getVariablesGroupedByCategory();

        $this->assertGreaterThan(0, $grouped->count());

        $customerCategory = $grouped->firstWhere('category', 'customer');
        $this->assertNotNull($customerCategory);
        $this->assertArrayHasKey('label', $customerCategory);
        $this->assertArrayHasKey('variables', $customerCategory);
        $this->assertArrayHasKey('color', $customerCategory);
    }

    // =======================================================
    // NOTIFICATION TYPE FILTERING TESTS
    // =======================================================

    /** @test */
    public function it_gets_variables_by_notification_type()
    {
        $variables = $this->registry->getVariablesByNotificationType('birthday_wish');

        $this->assertGreaterThan(0, $variables->count());
        $this->assertTrue($variables->contains('key', 'customer_name'));
    }

    /** @test */
    public function it_gets_suggested_variables_for_notification_type()
    {
        $suggested = $this->registry->getSuggestedVariables('policy_created');

        $this->assertIsArray($suggested);
        $this->assertContains('customer_name', $suggested);
        $this->assertContains('policy_number', $suggested);
    }

    /** @test */
    public function it_gets_required_context_for_notification_type()
    {
        $required = $this->registry->getRequiredContext('policy_created');

        $this->assertIsArray($required);
        $this->assertContains('customer', $required);
        $this->assertContains('insurance', $required);
    }

    /** @test */
    public function it_returns_all_variables_for_unknown_notification_type()
    {
        $variables = $this->registry->getVariablesByNotificationType('unknown_type');

        $this->assertGreaterThan(50, $variables->count());
    }

    // =======================================================
    // TEMPLATE EXTRACTION TESTS
    // =======================================================

    /** @test */
    public function it_extracts_variables_from_template()
    {
        $template = 'Hello {{customer_name}}, your policy {{policy_number}} expires on {{expiry_date}}.';

        $extracted = $this->registry->extractVariablesFromTemplate($template);

        $this->assertCount(3, $extracted);
        $this->assertContains('customer_name', $extracted);
        $this->assertContains('policy_number', $extracted);
        $this->assertContains('expiry_date', $extracted);
    }

    /** @test */
    public function it_extracts_attachment_variables_from_template()
    {
        $template = 'Your policy document: {{@policy_document}}';

        $extracted = $this->registry->extractVariablesFromTemplate($template);

        $this->assertContains('@policy_document', $extracted);
    }

    /** @test */
    public function it_extracts_unique_variables_from_template()
    {
        $template = 'Hi {{customer_name}}, {{customer_name}} your policy is ready.';

        $extracted = $this->registry->extractVariablesFromTemplate($template);

        $this->assertCount(1, $extracted);
        $this->assertContains('customer_name', $extracted);
    }

    /** @test */
    public function it_returns_empty_array_for_template_without_variables()
    {
        $template = 'This is a plain text message without any variables.';

        $extracted = $this->registry->extractVariablesFromTemplate($template);

        $this->assertEmpty($extracted);
    }

    // =======================================================
    // TEMPLATE VALIDATION TESTS
    // =======================================================

    /** @test */
    public function it_validates_template_with_all_valid_variables()
    {
        $template = 'Hello {{customer_name}}, your policy {{policy_number}} is ready.';

        $validation = $this->registry->validateTemplate($template, 'policy_created');

        $this->assertTrue($validation['valid']);
        $this->assertEmpty($validation['unknown']);
    }

    /** @test */
    public function it_detects_unknown_variables_in_template()
    {
        $template = 'Hello {{customer_name}}, your {{unknown_var}} is ready.';

        $validation = $this->registry->validateTemplate($template, 'policy_created');

        $this->assertFalse($validation['valid']);
        $this->assertContains('unknown_var', $validation['unknown']);
    }

    /** @test */
    public function it_detects_missing_suggested_variables()
    {
        $template = 'Hello {{customer_name}}.';

        $validation = $this->registry->validateTemplate($template, 'policy_created');

        $this->assertNotEmpty($validation['missing']);
        // policy_created should suggest policy_number but it's missing
        $this->assertContains('policy_number', $validation['missing']);
    }

    // =======================================================
    // VARIABLE TYPE FILTERING TESTS
    // =======================================================

    /** @test */
    public function it_gets_attachment_variables_only()
    {
        $attachments = $this->registry->getAttachmentVariables();

        $this->assertGreaterThan(0, $attachments->count());
        $this->assertTrue($attachments->every(fn ($var) => $var['type'] === 'attachment'));
    }

    /** @test */
    public function it_gets_computed_variables_only()
    {
        $computed = $this->registry->getComputedVariables();

        $this->assertGreaterThan(0, $computed->count());
        $this->assertTrue($computed->contains('key', 'days_remaining'));
        $this->assertTrue($computed->contains('key', 'policy_tenure'));
    }

    /** @test */
    public function it_gets_system_variables_only()
    {
        $system = $this->registry->getSystemVariables();

        $this->assertGreaterThan(0, $system->count());
        $this->assertTrue($system->contains('key', 'current_date'));
    }

    /** @test */
    public function it_gets_setting_variables_only()
    {
        $settings = $this->registry->getSettingVariables();

        $this->assertGreaterThan(0, $settings->count());
        $this->assertTrue($settings->contains('key', 'advisor_name'));
        $this->assertTrue($settings->contains('key', 'company_name'));
    }

    // =======================================================
    // UI DISPLAY TESTS
    // =======================================================

    /** @test */
    public function it_gets_variable_formatted_for_ui()
    {
        $ui = $this->registry->getVariableForUI('customer_name');

        $this->assertArrayHasKey('key', $ui);
        $this->assertArrayHasKey('label', $ui);
        $this->assertArrayHasKey('description', $ui);
        $this->assertArrayHasKey('sample', $ui);
        $this->assertArrayHasKey('color', $ui);
        $this->assertArrayHasKey('icon', $ui);
        $this->assertEquals('customer_name', $ui['key']);
    }

    /** @test */
    public function it_returns_empty_array_for_unknown_variable_ui()
    {
        $ui = $this->registry->getVariableForUI('unknown_var');

        $this->assertEmpty($ui);
    }

    /** @test */
    public function it_gets_all_variables_formatted_for_ui()
    {
        $allUi = $this->registry->getAllVariablesForUI();

        $this->assertGreaterThan(50, $allUi->count());

        $first = $allUi->first();
        $this->assertArrayHasKey('key', $first);
        $this->assertArrayHasKey('label', $first);
    }

    /** @test */
    public function it_gets_filtered_variables_formatted_for_ui()
    {
        $filtered = $this->registry->getAllVariablesForUI('birthday_wish');

        $this->assertGreaterThan(0, $filtered->count());
    }

    // =======================================================
    // VARIABLE METADATA STRUCTURE TESTS
    // =======================================================

    /** @test */
    public function all_variables_have_required_metadata()
    {
        $variables = $this->registry->getAllVariables();

        foreach ($variables as $variable) {
            $this->assertArrayHasKey('key', $variable);
            $this->assertArrayHasKey('label', $variable);
            $this->assertArrayHasKey('category', $variable);
            $this->assertArrayHasKey('source', $variable);
            $this->assertArrayHasKey('type', $variable);
            $this->assertArrayHasKey('sample', $variable);
        }
    }

    /** @test */
    public function all_categories_have_required_metadata()
    {
        $categories = $this->registry->getAllCategories();

        foreach ($categories as $category) {
            $this->assertArrayHasKey('key', $category);
            $this->assertArrayHasKey('label', $category);
            $this->assertArrayHasKey('color', $category);
            $this->assertArrayHasKey('icon', $category);
            $this->assertArrayHasKey('order', $category);
        }
    }

    /** @test */
    public function categories_are_sorted_by_order()
    {
        $categories = $this->registry->getAllCategories();

        $orders = $categories->pluck('order')->toArray();
        $sorted = $orders;
        sort($sorted);

        $this->assertEquals($sorted, $orders);
    }
}
