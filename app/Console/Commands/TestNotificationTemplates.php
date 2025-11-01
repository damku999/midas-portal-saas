<?php

namespace App\Console\Commands;

use App\Models\CustomerInsurance;
use App\Services\TemplateService;
use Illuminate\Console\Command;

class TestNotificationTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:test-templates
                            {--type= : Specific notification type code to test}
                            {--channel= : Specific channel to test (whatsapp, email)}
                            {--show-variables : Show available variables for each template}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all notification templates to verify they render correctly';

    protected TemplateService $templateService;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->templateService = app(TemplateService::class);

        $this->info('🧪 Testing Notification Templates');
        $this->newLine();

        // Get first customer insurance with relationships for testing
        $insurance = CustomerInsurance::with([
            'customer',
            'insuranceCompany',
            'policyType',
            'premiumType',
            'branch',
            'broker',
        ])->where('status', 1)->first();

        if (! $insurance) {
            $this->error('❌ No active customer insurance found for testing. Please create at least one policy first.');

            return self::FAILURE;
        }

        $this->info("Using test data from policy: {$insurance->policy_no} (Customer: {$insurance->customer->name})");
        $this->newLine();

        // Define templates to test
        $templateTests = $this->getTemplateTests();

        // Filter by options if provided
        if ($this->option('type')) {
            $templateTests = array_filter($templateTests, fn ($test) => $test['code'] === $this->option('type'));
        }

        if ($this->option('channel')) {
            $channel = $this->option('channel');
            $templateTests = array_filter($templateTests, fn ($test) => in_array($channel, $test['channels']));
        }

        $totalTests = count($templateTests);
        $passed = 0;
        $failed = 0;

        // Test each template
        foreach ($templateTests as $test) {
            foreach ($test['channels'] as $channel) {
                $this->testTemplate($test['code'], $channel, $insurance, $passed, $failed);
            }
        }

        $this->newLine();
        $this->info('📊 Test Summary');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Templates Tested', $passed + $failed],
                ['✅ Passed', $passed],
                ['❌ Failed', $failed],
                ['Success Rate', $passed + $failed > 0 ? round(($passed / ($passed + $failed)) * 100, 2).'%' : 'N/A'],
            ]
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Test a single template
     */
    protected function testTemplate(string $code, string $channel, CustomerInsurance $insurance, int &$passed, int &$failed): void
    {
        $this->info("Testing: {$code} ({$channel})");

        try {
            // Get template
            $template = $this->templateService->getTemplateByCode($code, $channel);

            if (! $template) {
                $this->warn('  ⚠️  Template not found in database');
                $failed++;

                return;
            }

            // Render template
            $rendered = $this->templateService->renderFromInsurance($code, $channel, $insurance);

            if (! $rendered) {
                $this->error('  ❌ Template rendering returned null');
                $failed++;

                return;
            }

            // Check for unresolved variables
            $unresolvedCount = substr_count($rendered, '{{');

            if ($unresolvedCount > 0) {
                $this->warn("  ⚠️  Found {$unresolvedCount} unresolved variables");
                // Show first 200 chars of rendered content
                $this->line('  Preview: '.substr(strip_tags($rendered), 0, 200).'...');

                if ($this->option('show-variables')) {
                    $this->showAvailableVariables($template);
                }

                $failed++;

                return;
            }

            // Success
            $this->line('  ✅ Rendered successfully ('.strlen($rendered).' chars)');

            if ($this->option('show-variables')) {
                $this->showAvailableVariables($template);
            }

            $passed++;

        } catch (\Exception $e) {
            $this->error("  ❌ Exception: {$e->getMessage()}");
            $failed++;
        }
    }

    /**
     * Show available variables for template
     */
    protected function showAvailableVariables($template): void
    {
        $variables = json_decode($template->available_variables ?? '[]', true);
        if (! empty($variables)) {
            $this->line('  Available Variables: '.implode(', ', $variables));
        }
    }

    /**
     * Get list of templates to test
     */
    protected function getTemplateTests(): array
    {
        return [
            ['code' => 'policy_created', 'channels' => ['whatsapp', 'email']],
            ['code' => 'renewal_30_days', 'channels' => ['whatsapp', 'email']],
            ['code' => 'renewal_15_days', 'channels' => ['whatsapp', 'email']],
            ['code' => 'renewal_7_days', 'channels' => ['whatsapp', 'email']],
            ['code' => 'renewal_expired', 'channels' => ['whatsapp', 'email']],
            ['code' => 'customer_welcome', 'channels' => ['whatsapp', 'email']],
            ['code' => 'birthday_wish', 'channels' => ['whatsapp']],
            ['code' => 'quotation_ready', 'channels' => ['whatsapp']],
            ['code' => 'claim_registered', 'channels' => ['whatsapp']],
            ['code' => 'claim_stage_update', 'channels' => ['whatsapp']],
            ['code' => 'claim_closed', 'channels' => ['whatsapp']],
            ['code' => 'document_request_health', 'channels' => ['whatsapp']],
            ['code' => 'document_request_vehicle', 'channels' => ['whatsapp']],
            ['code' => 'document_request_reminder', 'channels' => ['whatsapp']],
        ];
    }
}
