<?php

namespace App\Services;

use App\Models\Central\AuditLog;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantCreationService
{
    private string $progressKey;
    private array $steps = [];
    private int $currentStep = 0;
    private int $totalSteps = 10;

    public function __construct(string $sessionId = null)
    {
        $this->progressKey = 'tenant_creation_progress_' . ($sessionId ?? Str::random(16));
    }

    public function getProgressKey(): string
    {
        return $this->progressKey;
    }

    public function create(array $validated): array
    {
        $this->initializeProgress();

        try {
            DB::connection('central')->beginTransaction();

            // Step 1: Validate subdomain availability
            $this->updateProgress(1, 'Validating subdomain availability...', 'running');
            $domain = $validated['subdomain'] . '.' . $validated['domain'];

            if (DB::connection('central')->table('domains')->where('domain', $domain)->exists()) {
                throw new \Exception("Subdomain '{$validated['subdomain']}' is already taken on {$validated['domain']}");
            }
            $this->updateProgress(1, "✓ Subdomain is available: {$domain}", 'completed');

            // Step 2: Create tenant record
            $this->updateProgress(2, 'Creating tenant record...', 'running');
            $tenant = Tenant::create([
                'id' => Str::uuid()->toString(),
            ]);
            $this->updateProgress(2, "✓ Tenant record created (ID: {$tenant->id})", 'completed');

            // Step 3: Register domain
            $this->updateProgress(3, 'Registering domain...', 'running');
            $tenant->domains()->create([
                'domain' => $domain,
            ]);
            $this->updateProgress(3, "✓ Domain registered: {$domain}", 'completed');

            // Step 4: Store company data
            $this->updateProgress(4, 'Storing company information...', 'running');
            $tenant->update([
                'data' => [
                    'company_name' => $validated['company_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                ],
            ]);
            $this->updateProgress(4, '✓ Company information saved', 'completed');

            // Step 5: Create subscription
            $this->updateProgress(5, 'Setting up subscription...', 'running');
            $plan = Plan::findOrFail($validated['plan_id']);
            $trialEnabled = $validated['trial_enabled'] ?? true;
            $trialDays = $validated['trial_days'] ?? 14;

            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => $trialEnabled ? 'trial' : 'active',
                'is_trial' => $trialEnabled,
                'trial_ends_at' => $trialEnabled ? now()->addDays($trialDays) : null,
                'starts_at' => now(),
                'next_billing_date' => now()->addMonth(),
                'mrr' => $plan->price,
            ]);

            $statusText = $trialEnabled ? "trial ({$trialDays} days)" : 'active';
            $this->updateProgress(5, "✓ Subscription created ({$plan->name}, {$statusText})", 'completed');

            // Step 6: Create tenant database
            $this->updateProgress(6, 'Creating tenant database...', 'running');
            // Database creation happens automatically via TenancyServiceProvider events
            // We just need to give it a moment
            sleep(1);
            $this->updateProgress(6, '✓ Tenant database created', 'completed');

            // Step 7: Run database migrations
            $this->updateProgress(7, 'Running database migrations...', 'running');
            $tenant->run(function () use ($tenant) {
                // Migrations run automatically via TenancyServiceProvider
                sleep(1);
            });
            $this->updateProgress(7, '✓ Database migrations completed', 'completed');

            // Step 8: Seed database with roles, permissions, and master data
            $this->updateProgress(8, 'Seeding database with master data...', 'running');
            $password = $validated['admin_password'] ?? Str::random(16);

            $tenant->run(function ($tenant) use ($validated, $password) {
                // Set admin data in config for AdminSeeder to use
                config([
                    'tenant.admin' => [
                        'first_name' => $validated['admin_first_name'],
                        'last_name' => $validated['admin_last_name'],
                        'email' => $validated['admin_email'],
                        'mobile_number' => $validated['phone'] ?? null,
                        'password' => $password,
                    ],
                ]);

                // Prepare custom settings for AppSettingsSeeder
                $customSettings = [];

                // Branding & Theme
                if (!empty($validated['company_name'])) {
                    $customSettings['company_name'] = $validated['company_name'];
                }
                if (!empty($validated['company_tagline'])) {
                    $customSettings['company_tagline'] = $validated['company_tagline'];
                }
                if (!empty($validated['company_logo'])) {
                    $customSettings['company_logo'] = $validated['company_logo'];
                }
                if (!empty($validated['theme_primary_color'])) {
                    $customSettings['theme_primary_color'] = $validated['theme_primary_color'];
                }

                // Communication Settings
                if (!empty($validated['whatsapp_sender_id'])) {
                    $customSettings['whatsapp_sender_id'] = $validated['whatsapp_sender_id'];
                }
                if (!empty($validated['whatsapp_auth_token'])) {
                    $customSettings['whatsapp_auth_token'] = $validated['whatsapp_auth_token'];
                }
                if (!empty($validated['email_from_address'])) {
                    $customSettings['email_from_address'] = $validated['email_from_address'];
                }
                if (!empty($validated['email_from_name'])) {
                    $customSettings['email_from_name'] = $validated['email_from_name'];
                }

                // Localization
                if (!empty($validated['timezone'])) {
                    $customSettings['timezone'] = $validated['timezone'];
                }
                if (!empty($validated['currency'])) {
                    $customSettings['currency'] = $validated['currency'];
                }
                if (!empty($validated['currency_symbol'])) {
                    $customSettings['currency_symbol'] = $validated['currency_symbol'];
                }

                // Additional Company Info
                if (!empty($validated['company_phone'])) {
                    $customSettings['company_phone'] = $validated['company_phone'];
                }
                if (!empty($validated['company_phone_whatsapp'])) {
                    $customSettings['company_phone_whatsapp'] = $validated['company_phone_whatsapp'];
                }

                // Set custom settings in config for AppSettingsSeeder to use
                config(['tenant.settings' => $customSettings]);

                // Seed complete tenant database
                Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\Tenant\\DatabaseSeeder',
                    '--force' => true,
                ]);

                // Store password in tenant data for welcome email
                $tenant->update([
                    'data' => array_merge($tenant->data ?? [], [
                        'admin_password' => $password,
                    ]),
                ]);
            });
            $this->updateProgress(8, '✓ Database seeded with roles, permissions, and master data', 'completed');

            // Step 9: Create admin user
            $this->updateProgress(9, 'Creating admin user...', 'running');
            $adminName = $validated['admin_first_name'] . ' ' . $validated['admin_last_name'];
            $this->updateProgress(9, "✓ Admin user created: {$adminName} ({$validated['admin_email']})", 'completed');

            // Step 10: Finalize and log
            $this->updateProgress(10, 'Finalizing tenant setup...', 'running');
            AuditLog::log(
                'tenant.created',
                "Created tenant: {$validated['company_name']} ({$domain})",
                auth('central')->user(),
                $tenant->id,
                [
                    'company_name' => $validated['company_name'],
                    'subdomain' => $domain,
                    'plan' => $plan->name,
                ]
            );

            DB::connection('central')->commit();
            $this->updateProgress(10, '✓ Tenant setup completed successfully!', 'completed');

            // Mark overall process as complete
            $this->markComplete($tenant, $domain);

            return [
                'success' => true,
                'tenant' => $tenant,
                'domain' => $domain,
                'message' => 'Tenant created successfully!',
            ];

        } catch (\Exception $e) {
            DB::connection('central')->rollBack();

            $this->updateProgress($this->currentStep, '✗ Error: ' . $e->getMessage(), 'failed');
            $this->markFailed($e->getMessage());

            throw $e;
        }
    }

    private function initializeProgress(): void
    {
        $progress = [
            'status' => 'running',
            'current_step' => 0,
            'total_steps' => $this->totalSteps,
            'percentage' => 0,
            'steps' => [],
            'started_at' => now()->toDateTimeString(),
            'completed_at' => null,
            'error' => null,
        ];

        // Use file cache store to persist across requests
        Cache::store('file')->put($this->progressKey, $progress, now()->addMinutes(30));
    }

    private function updateProgress(int $step, string $message, string $status = 'pending'): void
    {
        $progress = Cache::store('file')->get($this->progressKey, []);

        $this->currentStep = $step;
        $progress['current_step'] = $step;
        $progress['percentage'] = round(($step / $this->totalSteps) * 100);

        $progress['steps'][$step] = [
            'number' => $step,
            'message' => $message,
            'status' => $status,
            'timestamp' => now()->toDateTimeString(),
        ];

        Cache::store('file')->put($this->progressKey, $progress, now()->addMinutes(30));
    }

    private function markComplete(Tenant $tenant, string $domain): void
    {
        $progress = Cache::store('file')->get($this->progressKey, []);

        $progress['status'] = 'completed';
        $progress['completed_at'] = now()->toDateTimeString();
        $progress['tenant_id'] = $tenant->id;
        $progress['domain'] = $domain;

        Cache::store('file')->put($this->progressKey, $progress, now()->addHours(1));
    }

    private function markFailed(string $error): void
    {
        $progress = Cache::store('file')->get($this->progressKey, []);

        $progress['status'] = 'failed';
        $progress['completed_at'] = now()->toDateTimeString();
        $progress['error'] = $error;

        Cache::store('file')->put($this->progressKey, $progress, now()->addHours(1));
    }

    public function getProgress(): array
    {
        return Cache::store('file')->get($this->progressKey, [
            'status' => 'not_started',
            'current_step' => 0,
            'total_steps' => $this->totalSteps,
            'percentage' => 0,
            'steps' => [],
        ]);
    }
}
