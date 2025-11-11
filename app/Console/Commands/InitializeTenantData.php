<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InitializeTenantData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:init-data {--tenant= : Specific tenant ID to initialize}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize data field for tenants with NULL data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->option('tenant');

        $query = Tenant::with('domains');

        if ($tenantId) {
            $query->where('id', $tenantId);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->error('No tenants found!');
            return 1;
        }

        $this->info("Initializing data for {$tenants->count()} tenant(s)...");
        $bar = $this->output->createProgressBar($tenants->count());

        $initialized = 0;
        $skipped = 0;

        foreach ($tenants as $tenant) {
            // Check if data is null or empty using raw query
            $rawData = DB::connection('central')
                ->table('tenants')
                ->where('id', $tenant->id)
                ->value('data');

            if (is_null($rawData) || $rawData === 'null' || $rawData === '{}') {
                $domain = $tenant->domains->first();
                $companyName = $domain ? str_replace(['.midastech.testing.in', '.midastech.in', '.localhost'], '', $domain->domain) : 'Company';
                $companyName = ucfirst($companyName);
                $email = $domain ? ('admin@' . $domain->domain) : 'admin@example.com';

                // Update using raw query to ensure it saves
                DB::connection('central')
                    ->table('tenants')
                    ->where('id', $tenant->id)
                    ->update([
                        'data' => json_encode([
                            'company_name' => $companyName,
                            'email' => $email,
                            'phone' => null,
                        ])
                    ]);

                $this->newLine();
                $this->info("✓ Initialized: {$companyName} ({$tenant->id})");
                $initialized++;
            } else {
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Summary:");
        $this->table(
            ['Status', 'Count'],
            [
                ['Initialized', $initialized],
                ['Skipped (already set)', $skipped],
                ['Total', $tenants->count()],
            ]
        );

        return 0;
    }
}
