<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Services\UsageAlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckUsageThresholds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usage:check-thresholds
                            {--tenant= : Check specific tenant ID only}
                            {--resource= : Check specific resource type only (users, customers, storage)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check usage thresholds for all tenants and create alerts for those exceeding limits';

    /**
     * Execute the console command.
     */
    public function handle(UsageAlertService $usageAlertService): int
    {
        $this->info('Starting usage threshold checks...');

        $specificTenantId = $this->option('tenant');
        $specificResource = $this->option('resource');

        // Validate resource type if specified
        if ($specificResource && !in_array($specificResource, ['users', 'customers', 'storage'])) {
            $this->error("Invalid resource type: {$specificResource}");
            $this->info('Valid types: users, customers, storage');
            return Command::FAILURE;
        }

        // Get tenants to check
        $tenantsQuery = Tenant::query()->with(['subscription.plan']);

        if ($specificTenantId) {
            $tenantsQuery->where('id', $specificTenantId);
        }

        $tenants = $tenantsQuery->get();

        if ($tenants->isEmpty()) {
            $this->warn('No tenants found to check.');
            return Command::SUCCESS;
        }

        $this->info("Checking {$tenants->count()} tenant(s)...");

        $totalAlerts = 0;
        $tenantsWithAlerts = 0;
        $resourceTypes = $specificResource ? [$specificResource] : [];

        // Progress bar for better UX
        $progressBar = $this->output->createProgressBar($tenants->count());
        $progressBar->start();

        foreach ($tenants as $tenant) {
            try {
                // Check thresholds for this tenant
                $alerts = $usageAlertService->checkTenantThresholds($tenant, $resourceTypes);

                if (!empty($alerts)) {
                    $tenantsWithAlerts++;
                    $totalAlerts += count($alerts);

                    $companyName = $tenant->data['company_name'] ?? $tenant->domains->first()?->domain ?? $tenant->id;
                    $this->newLine();
                    $this->warn("  └─ {$companyName}: " . count($alerts) . ' alert(s) created');

                    foreach ($alerts as $alert) {
                        $this->line("     • {$alert->resource_type_display}: {$alert->usage_percentage}% ({$alert->threshold_display})");
                    }
                }

                $progressBar->advance();

            } catch (\Exception $e) {
                $this->newLine();
                $this->error("  └─ Error checking tenant {$tenant->id}: {$e->getMessage()}");

                Log::error('Failed to check usage thresholds for tenant', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('=== Usage Check Summary ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Tenants Checked', $tenants->count()],
                ['Tenants with Alerts', $tenantsWithAlerts],
                ['Total Alerts Created', $totalAlerts],
            ]
        );

        // Global statistics
        $stats = $usageAlertService->getGlobalAlertStatistics();
        $this->newLine();
        $this->info('=== Global Alert Statistics ===');
        $this->table(
            ['Status', 'Tenants Affected'],
            [
                ['⚠️  Warning (80%)', $stats['tenants_with_warnings']],
                ['🚨 Critical (90%)', $stats['tenants_with_critical']],
                ['⛔ Exceeded (100%)', $stats['tenants_exceeded']],
            ]
        );

        if ($totalAlerts > 0) {
            $this->newLine();
            $this->warn("Created {$totalAlerts} new alert(s). Email notifications have been sent.");
        } else {
            $this->newLine();
            $this->info('✓ All tenants are within their usage limits.');
        }

        Log::info('Usage threshold check completed', [
            'tenants_checked' => $tenants->count(),
            'tenants_with_alerts' => $tenantsWithAlerts,
            'total_alerts_created' => $totalAlerts,
            'specific_tenant' => $specificTenantId,
            'specific_resource' => $specificResource,
        ]);

        return Command::SUCCESS;
    }
}
