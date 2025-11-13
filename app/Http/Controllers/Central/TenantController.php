<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\AuditLog;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use App\Services\TenantCreationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants.
     */
    public function index(Request $request)
    {
        $query = Tenant::with(['subscription.plan', 'domains']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('domains', function ($domainQuery) use ($search) {
                        $domainQuery->where('domain', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->active();
                    break;
                case 'trial':
                    $query->onTrial();
                    break;
                case 'suspended':
                    $query->suspended();
                    break;
            }
        }

        // Filter by plan
        if ($request->filled('plan_id')) {
            $query->whereHas('subscription', function ($q) use ($request) {
                $q->where('plan_id', $request->plan_id);
            });
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $tenants = $query->paginate(20);
        $plans = Plan::active()->ordered()->get();

        return view('central.tenants.index', compact('tenants', 'plans'));
    }

    /**
     * Show the form for creating a new tenant.
     */
    public function create()
    {
        $plans = Plan::active()->ordered()->get();

        return view('central.tenants.create', compact('plans'));
    }

    /**
     * Store a newly created tenant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'subdomain' => [
                'required',
                'string',
                'alpha_dash',
                'max:63',
            ],
            'domain' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    // Validate the full domain (subdomain + base domain) for uniqueness
                    $fullDomain = $request->subdomain . '.' . $value;
                    if (DB::connection('central')->table('domains')->where('domain', $fullDomain)->exists()) {
                        $fail("The subdomain {$request->subdomain} is already taken on {$value}.");
                    }

                    // Validate domain is in allowed list
                    $allowedDomains = array_keys(config('tenancy-domains.domains', []));
                    if (!in_array($value, $allowedDomains)) {
                        $fail("The selected domain is not valid.");
                    }
                },
            ],
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'plan_id' => 'required|exists:plans,id',
            'subscription_type' => ['required', Rule::in(['trial', 'paid'])],
            'trial_days' => [
                'nullable',
                'integer',
                'min:1',
                'max:90',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->subscription_type === 'paid' && $value) {
                        $fail('Trial days cannot be set for paid subscriptions.');
                    }
                    if ($request->subscription_type === 'trial' && !$value) {
                        $fail('Trial days is required for trial subscriptions.');
                    }
                },
            ],
            'admin_first_name' => 'required|string|max:255',
            'admin_last_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'nullable|string|min:8',
            'send_welcome_email' => 'boolean',

            // Database configuration
            'db_name' => 'nullable|string|max:64',
            'db_prefix' => 'nullable|string|max:32',
            'db_create_database' => 'nullable|boolean',
            'db_run_migrations' => 'nullable|boolean',
            'db_run_seeders' => 'nullable|boolean',
        ]);

        $tenant = null;
        $databaseCreated = false;
        $dbName = null;

        DB::connection('central')->beginTransaction();

        try {
            // Create tenant
            $tenant = Tenant::create([
                'id' => Str::uuid()->toString(),
            ]);

            // Create domain
            $domain = $validated['subdomain'].'.'.$validated['domain'];
            $tenant->domains()->create([
                'domain' => $domain,
            ]);

            // Store company data using VirtualColumn attributes
            $tenant->company_name = $validated['company_name'];
            $tenant->email = $validated['email'];
            $tenant->phone = $validated['phone'] ?? null;
            $tenant->save();

            // Create subscription
            $plan = Plan::findOrFail($validated['plan_id']);
            $isTrial = $validated['subscription_type'] === 'trial';
            $trialDays = $isTrial ? ($validated['trial_days'] ?? 14) : 0;

            // Calculate ends_at date based on billing interval
            $endsAt = null;
            if (!$isTrial) {
                // For paid subscriptions, set end date based on billing interval
                $endsAt = $this->calculateSubscriptionEndDate($plan->billing_interval);
            }

            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => $isTrial ? 'trial' : 'active',
                'is_trial' => $isTrial,
                'trial_ends_at' => $isTrial ? now()->addDays($trialDays) : null,
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'next_billing_date' => $isTrial ? now()->addDays($trialDays) : now()->addMonth(),
                'mrr' => $plan->price,
            ]);

            // Store database configuration options using setInternal() for tenancy package compatibility
            if (!empty($validated['db_name'])) {
                $tenant->setInternal('db_name', $validated['db_name']);
            }
            if (!empty($validated['db_username'])) {
                $tenant->setInternal('db_username', $validated['db_username']);
            }
            if (!empty($validated['db_password'])) {
                $tenant->setInternal('db_password', $validated['db_password']);
            }
            if (!empty($validated['db_host'])) {
                $tenant->setInternal('db_host', $validated['db_host']);
            }
            if (!empty($validated['db_port'])) {
                $tenant->setInternal('db_port', $validated['db_port']);
            }

            // Store create_database as internal key (Stancl CreateDatabase job checks this)
            $dbCreateEnabled = $validated['db_create_database'] ?? true;
            $tenant->setInternal('create_database', $dbCreateEnabled);

            // Store other configuration flags in data column
            $tenant->db_prefix = $validated['db_prefix'] ?? 'tenant_';
            $tenant->db_run_migrations = $validated['db_run_migrations'] ?? true;
            $tenant->db_run_seeders = $validated['db_run_seeders'] ?? true;
            $tenant->save();

            // Track database creation for rollback
            if ($dbCreateEnabled) {
                $dbName = $tenant->getInternal('db_name') ?? config('tenancy.database.prefix', '') . $tenant->id;
                sleep(1); // Give time for database creation
                $databaseCreated = true;
            }

            // Get configuration flags for conditional processing
            $migrationsEnabled = $validated['db_run_migrations'] ?? true;
            $seedersEnabled = $validated['db_run_seeders'] ?? true;

            if ($seedersEnabled) {
                $tenant->run(function ($tenant) use ($validated) {
                    $password = $validated['admin_password'] ?? Str::random(16);

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

                    // Seed complete tenant database (includes roles, permissions, admin user, and all master data)
                    \Artisan::call('db:seed', [
                        '--class' => 'Database\\Seeders\\Tenant\\DatabaseSeeder',
                        '--force' => true,
                    ]);

                    // Store password in tenant data for welcome email (VirtualColumn attribute)
                    $tenant->admin_password = $password;
                    $tenant->save();
                });
            }

            // Log action
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

            return redirect()
                ->route('central.tenants.show', $tenant)
                ->with('success', 'Tenant created successfully!');

        } catch (\Exception $e) {
            // Rollback central database transaction
            DB::connection('central')->rollBack();

            // CRITICAL: Clean up the physical tenant database if it was created
            if ($databaseCreated && $dbName && $tenant) {
                try {
                    DB::connection('central')->statement("DROP DATABASE IF EXISTS `{$dbName}`");
                } catch (\Exception $dbEx) {
                    \Log::error('Failed to delete tenant database during rollback', [
                        'tenant_id' => $tenant?->id,
                        'database_name' => $dbName,
                        'error' => $dbEx->getMessage(),
                    ]);
                }
            }

            return back()
                ->withInput()
                ->with('error', 'Failed to create tenant: '.$e->getMessage());
        }
    }

    /**
     * Display the specified tenant.
     */
    public function show(Tenant $tenant)
    {
        $tenant->load(['subscription.plan', 'domains', 'auditLogs' => function ($query) {
            $query->latest()->limit(20);
        }]);

        $recentActivity = $tenant->auditLogs;

        return view('central.tenants.show', compact('tenant', 'recentActivity'));
    }

    /**
     * Show the form for editing the specified tenant.
     */
    public function edit(Tenant $tenant)
    {
        $tenant->load(['subscription.plan']);
        $plans = Plan::active()->ordered()->get();

        return view('central.tenants.edit', compact('tenant', 'plans'));
    }

    /**
     * Update the specified tenant.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            // Basic Information
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',

            // Subscription (only required if tenant has subscription)
            'plan_id' => 'nullable|exists:plans,id',
            'status' => [
                $tenant->subscription ? 'required' : 'nullable',
                Rule::in(['trial', 'active', 'suspended', 'cancelled', 'expired'])
            ],
            'trial_ends_at' => 'nullable|date',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'next_billing_date' => 'nullable|date',

            // Branding & Theme
            'company_tagline' => 'nullable|string|max:255',
            'company_logo' => 'nullable|file|image|mimes:png,jpg,jpeg,svg|max:2048',
            'theme_primary_color' => 'nullable|string|max:7',

            // Communication Settings
            'whatsapp_sender_id' => 'nullable|string|max:20',
            'whatsapp_auth_token' => 'nullable|string|max:255',
            'email_from_address' => 'nullable|email|max:255',
            'email_from_name' => 'nullable|string|max:255',

            // Localization
            'timezone' => 'nullable|string|timezone',
            'currency' => 'nullable|string|size:3',
            'currency_symbol' => 'nullable|string|max:5',

            // Database configuration
            'db_name' => 'nullable|string|max:64',
            'db_prefix' => 'nullable|string|max:32',
            'db_username' => 'nullable|string|max:64',
            'db_password' => 'nullable|string|max:255',
            'db_host' => 'nullable|string|max:255',
            'db_port' => 'nullable|integer|min:1|max:65535',
        ]);

        DB::connection('central')->beginTransaction();

        try {
            // Handle logo upload
            if ($request->hasFile('company_logo')) {
                $logoPath = $request->file('company_logo')->store('tenant-logos', 'public');

                // Copy to tenant storage
                $centralFullPath = storage_path('app/public/' . $logoPath);
                if (file_exists($centralFullPath)) {
                    $tenant->run(function () use ($centralFullPath, $logoPath) {
                        $tenantLogoPath = 'company_logo.' . pathinfo($logoPath, PATHINFO_EXTENSION);
                        \Illuminate\Support\Facades\Storage::disk('public')->put(
                            $tenantLogoPath,
                            file_get_contents($centralFullPath)
                        );
                        return $tenantLogoPath;
                    });
                }
                $validated['company_logo'] = $logoPath;
            }

            // Update tenant data using VirtualColumn attributes
            // The VirtualColumn trait automatically serializes these into the 'data' column
            $tenant->company_name = $validated['company_name'];
            $tenant->email = $validated['email'];
            $tenant->phone = $validated['phone'] ?? null;
            $tenant->company_tagline = $validated['company_tagline'] ?? null;
            $tenant->company_logo = $validated['company_logo'] ?? $tenant->company_logo;
            $tenant->theme_primary_color = $validated['theme_primary_color'] ?? null;
            $tenant->whatsapp_sender_id = $validated['whatsapp_sender_id'] ?? null;
            $tenant->whatsapp_auth_token = $validated['whatsapp_auth_token'] ?? null;
            $tenant->email_from_address = $validated['email_from_address'] ?? null;
            $tenant->email_from_name = $validated['email_from_name'] ?? null;
            $tenant->timezone = $validated['timezone'] ?? null;
            $tenant->currency = $validated['currency'] ?? null;
            $tenant->currency_symbol = $validated['currency_symbol'] ?? null;

            // Update database configuration using setInternal() for tenancy package compatibility
            if (!empty($validated['db_name'])) {
                $tenant->setInternal('db_name', $validated['db_name']);
            }
            if (!empty($validated['db_username'])) {
                $tenant->setInternal('db_username', $validated['db_username']);
            }
            if (!empty($validated['db_password'])) {
                $tenant->setInternal('db_password', $validated['db_password']);
            }
            if (!empty($validated['db_host'])) {
                $tenant->setInternal('db_host', $validated['db_host']);
            }
            if (!empty($validated['db_port'])) {
                $tenant->setInternal('db_port', $validated['db_port']);
            }
            if (isset($validated['db_prefix'])) {
                $tenant->db_prefix = $validated['db_prefix'];
            }

            $tenant->save();

            // Update subscription if changed
            if ($tenant->subscription) {
                $subscriptionUpdates = [];
                $auditChanges = [];

                if (isset($validated['status']) && $tenant->subscription->status !== $validated['status']) {
                    $subscriptionUpdates['status'] = $validated['status'];
                    $auditChanges['status'] = ['old' => $tenant->subscription->status, 'new' => $validated['status']];

                    AuditLog::log(
                        'tenant.status_changed',
                        "Changed tenant status to: {$validated['status']}",
                        auth('central')->user(),
                        $tenant->id,
                        ['old_status' => $tenant->subscription->status, 'new_status' => $validated['status']]
                    );
                }

                if (isset($validated['plan_id']) && $tenant->subscription->plan_id !== $validated['plan_id']) {
                    $oldPlan = $tenant->subscription->plan;
                    $newPlan = Plan::find($validated['plan_id']);

                    $subscriptionUpdates['plan_id'] = $validated['plan_id'];
                    $subscriptionUpdates['mrr'] = $newPlan->price;
                    $auditChanges['plan'] = ['old' => $oldPlan->name, 'new' => $newPlan->name];

                    AuditLog::log(
                        'tenant.plan_changed',
                        "Changed plan from {$oldPlan->name} to {$newPlan->name}",
                        auth('central')->user(),
                        $tenant->id,
                        ['old_plan' => $oldPlan->name, 'new_plan' => $newPlan->name]
                    );
                }

                // Handle subscription dates
                if (isset($validated['trial_ends_at']) && $tenant->subscription->is_trial) {
                    $oldDate = $tenant->subscription->trial_ends_at ? $tenant->subscription->trial_ends_at->format('Y-m-d') : null;
                    if ($oldDate !== $validated['trial_ends_at']) {
                        $subscriptionUpdates['trial_ends_at'] = $validated['trial_ends_at'];
                        $auditChanges['trial_ends_at'] = ['old' => $oldDate, 'new' => $validated['trial_ends_at']];
                    }
                }

                if (isset($validated['starts_at'])) {
                    $oldDate = $tenant->subscription->starts_at ? $tenant->subscription->starts_at->format('Y-m-d') : null;
                    if ($oldDate !== $validated['starts_at']) {
                        $subscriptionUpdates['starts_at'] = $validated['starts_at'];
                        $auditChanges['starts_at'] = ['old' => $oldDate, 'new' => $validated['starts_at']];
                    }
                }

                if (isset($validated['ends_at'])) {
                    $oldDate = $tenant->subscription->ends_at ? $tenant->subscription->ends_at->format('Y-m-d') : null;
                    if ($oldDate !== $validated['ends_at']) {
                        $subscriptionUpdates['ends_at'] = $validated['ends_at'];
                        $auditChanges['ends_at'] = ['old' => $oldDate, 'new' => $validated['ends_at']];
                    }
                }

                if (isset($validated['next_billing_date'])) {
                    $oldDate = $tenant->subscription->next_billing_date ? $tenant->subscription->next_billing_date->format('Y-m-d') : null;
                    if ($oldDate !== $validated['next_billing_date']) {
                        $subscriptionUpdates['next_billing_date'] = $validated['next_billing_date'];
                        $auditChanges['next_billing_date'] = ['old' => $oldDate, 'new' => $validated['next_billing_date']];
                    }
                }

                if (!empty($subscriptionUpdates)) {
                    $tenant->subscription->update($subscriptionUpdates);

                    // Log subscription date changes if any
                    if (!empty($auditChanges)) {
                        AuditLog::log(
                            'tenant.subscription_updated',
                            "Updated subscription details",
                            auth('central')->user(),
                            $tenant->id,
                            ['changes' => $auditChanges]
                        );
                    }
                }
            }

            DB::connection('central')->commit();

            return redirect()
                ->route('central.tenants.show', $tenant)
                ->with('success', 'Tenant updated successfully!');

        } catch (\Exception $e) {
            DB::connection('central')->rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to update tenant: ' . $e->getMessage());
        }
    }

    /**
     * Suspend the specified tenant.
     */
    public function suspend(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        if ($tenant->subscription) {
            $tenant->subscription->suspend();

            $companyName = $tenant->company_name ?? $tenant->domains->first()->domain ?? 'Unknown';
            AuditLog::log(
                'tenant.suspended',
                "Suspended tenant: {$companyName}",
                auth('central')->user(),
                $tenant->id,
                ['reason' => $validated['reason'] ?? 'No reason provided']
            );
        }

        return redirect()
            ->route('central.tenants.show', $tenant)
            ->with('success', 'Tenant suspended successfully!');
    }

    /**
     * Activate the specified tenant.
     */
    public function activate(Tenant $tenant)
    {
        if ($tenant->subscription) {
            $tenant->subscription->resume();

            $companyName = $tenant->company_name ?? $tenant->domains->first()->domain ?? 'Unknown';
            AuditLog::log(
                'tenant.activated',
                "Activated tenant: {$companyName}",
                auth('central')->user(),
                $tenant->id
            );
        }

        return redirect()
            ->route('central.tenants.show', $tenant)
            ->with('success', 'Tenant activated successfully!');
    }

    /**
     * End trial immediately and convert to paid subscription.
     */
    public function endTrial(Tenant $tenant)
    {
        if (!$tenant->subscription || !$tenant->subscription->is_trial) {
            return back()->with('error', 'This tenant is not on a trial subscription.');
        }

        DB::connection('central')->beginTransaction();

        try {
            $tenant->subscription->update([
                'is_trial' => false,
                'status' => 'active',
                'trial_ends_at' => now(),
                'starts_at' => now(),
                'ends_at' => $this->calculateSubscriptionEndDate($tenant->subscription->plan->billing_interval),
                'next_billing_date' => $this->calculateSubscriptionEndDate($tenant->subscription->plan->billing_interval),
            ]);

            $companyName = $tenant->company_name ?? $tenant->domains->first()->domain ?? 'Unknown';
            AuditLog::log(
                'tenant.trial_ended',
                "Ended trial for tenant: {$companyName}",
                auth('central')->user(),
                $tenant->id,
                ['trial_ended_at' => now()->toDateTimeString()]
            );

            DB::connection('central')->commit();

            return redirect()
                ->route('central.tenants.show', $tenant)
                ->with('success', 'Trial ended successfully! Tenant is now on active subscription.');

        } catch (\Exception $e) {
            DB::connection('central')->rollBack();

            return back()->with('error', 'Failed to end trial: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified tenant.
     */
    public function destroy(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'confirmation' => 'required|string',
            'delete_database' => 'nullable|boolean',
            'delete_files' => 'nullable|boolean',
            'delete_domains' => 'nullable|boolean',
            'delete_subscription' => 'nullable|boolean',
        ]);

        // Use same fallback logic as the view
        $companyName = $tenant->company_name ?? ($tenant->domains->first()->domain ?? $tenant->id);
        $expectedConfirmation = 'DELETE '.strtoupper($companyName);

        if ($validated['confirmation'] !== $expectedConfirmation) {
            return back()->with('error', 'Confirmation text does not match. Tenant not deleted.');
        }

        // Default all deletion options to true if not specified
        $deleteDatabase = $validated['delete_database'] ?? true;
        $deleteFiles = $validated['delete_files'] ?? true;
        $deleteDomains = $validated['delete_domains'] ?? true;
        $deleteSubscription = $validated['delete_subscription'] ?? true;

        DB::connection('central')->beginTransaction();

        try {
            $deletionLog = [];

            // Note: Subscription, domains, and audit logs are automatically deleted
            // via Tenant model's deleting event. We just track them for logging.
            if ($tenant->subscription) {
                $deletionLog[] = 'subscription';
            }

            // Track audit logs for logging
            $auditLogCount = AuditLog::where('tenant_id', $tenant->id)->count();
            if ($auditLogCount > 0) {
                $deletionLog[] = "audit_logs ({$auditLogCount} records)";
            }

            // Track domains for logging
            if ($tenant->domains()->count() > 0) {
                $deletionLog[] = 'domains (' . $tenant->domains()->count() . ' records)';
            }

            // Delete files if requested
            if ($deleteFiles) {
                try {
                    // Delete tenant-specific storage directories
                    // Stancl Tenancy uses: storage/tenant{id}/app/ structure
                    $tenantStoragePath = storage_path("tenant{$tenant->id}");

                    if (is_dir($tenantStoragePath)) {
                        \Illuminate\Support\Facades\File::deleteDirectory($tenantStoragePath);
                        $deletionLog[] = 'files & uploads (tenant storage)';
                    } else {
                        $deletionLog[] = 'files (no tenant storage directory found)';
                    }
                } catch (\Exception $e) {
                    $deletionLog[] = 'files (deletion failed: ' . $e->getMessage() . ')';
                }
            }

            // Log deletion before deleting tenant
            AuditLog::log(
                'tenant.deleted',
                "Deleted tenant: {$companyName}",
                auth('central')->user(),
                $tenant->id,
                [
                    'company_name' => $companyName,
                    'deleted_items' => $deletionLog,
                    'delete_database' => $deleteDatabase,
                ]
            );

            // Delete tenant record
            // When delete() is called, it triggers TenantDeleted event
            // which will delete the actual database via DeleteDatabase job (if configured)
            if (!$deleteDatabase) {
                // If database should be preserved, temporarily disable the DeleteDatabase job
                // by setting a flag that the event listener can check (VirtualColumn attribute)
                $tenant->preserve_database = true;
                $tenant->saveQuietly();
            }

            // Delete tenant record - this triggers TenantDeleted event
            $tenant->delete();

            DB::connection('central')->commit();

            $message = $deleteDatabase
                ? 'Tenant and database deleted successfully!'
                : 'Tenant deleted successfully! (Database preserved)';

            return redirect()
                ->route('central.tenants.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::connection('central')->rollBack();

            return back()->with('error', 'Failed to delete tenant: '.$e->getMessage());
        }
    }

    /**
     * Create tenant with progress tracking (AJAX endpoint).
     */
    public function storeWithProgress(Request $request)
    {
        $validated = $request->validate([
            // Required basic fields
            'company_name' => 'required|string|max:255',
            'subdomain' => [
                'required',
                'string',
                'alpha_dash',
                'max:63',
            ],
            'domain' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    // Validate the full domain (subdomain + base domain) for uniqueness
                    $fullDomain = $request->subdomain . '.' . $value;
                    if (DB::connection('central')->table('domains')->where('domain', $fullDomain)->exists()) {
                        $fail("The subdomain {$request->subdomain} is already taken on {$value}.");
                    }

                    // Validate domain is in allowed list
                    $allowedDomains = array_keys(config('tenancy-domains.domains', []));
                    if (!in_array($value, $allowedDomains)) {
                        $fail("The selected domain is not valid.");
                    }
                },
            ],
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'plan_id' => 'required|exists:plans,id',
            'subscription_type' => ['required', Rule::in(['trial', 'paid'])],
            'trial_days' => [
                'nullable',
                'integer',
                'min:1',
                'max:90',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->subscription_type === 'paid' && $value) {
                        $fail('Trial days cannot be set for paid subscriptions.');
                    }
                    if ($request->subscription_type === 'trial' && !$value) {
                        $fail('Trial days is required for trial subscriptions.');
                    }
                },
            ],

            // Admin user fields
            'admin_first_name' => 'required|string|max:255',
            'admin_last_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'nullable|string|min:8',
            'send_welcome_email' => 'boolean',

            // Optional branding & theme fields
            'company_tagline' => 'nullable|string|max:255',
            'company_logo' => 'nullable|file|image|mimes:png,jpg,jpeg,svg|max:2048',
            'theme_primary_color' => 'nullable|string|max:7',

            // Optional WhatsApp communication fields
            'whatsapp_sender_id' => 'nullable|string|max:20',
            'whatsapp_auth_token' => 'nullable|string|max:255',

            // Optional email communication fields
            'email_from_address' => 'nullable|email|max:255',
            'email_from_name' => 'nullable|string|max:255',

            // Optional localization fields
            'timezone' => 'nullable|string|timezone',
            'currency' => 'nullable|string|size:3',
            'currency_symbol' => 'nullable|string|max:5',

            // Additional optional company fields
            'company_phone' => 'nullable|string|max:20',
            'company_phone_whatsapp' => 'nullable|string|max:20',

            // Database configuration
            'db_name' => 'nullable|string|max:64',
            'db_prefix' => 'nullable|string|max:32',
            'db_create_database' => 'nullable|boolean',
            'db_run_migrations' => 'nullable|boolean',
            'db_run_seeders' => 'nullable|boolean',
        ]);

        // Handle logo file upload if provided
        if ($request->hasFile('company_logo')) {
            $logoPath = $request->file('company_logo')->store('tenant-logos', 'public');
            $validated['company_logo'] = $logoPath;
        }

        $sessionId = $request->input('session_id', Str::random(16));
        $service = new TenantCreationService($sessionId);

        try {
            $result = $service->create($validated);

            return response()->json([
                'success' => true,
                'progress_key' => $service->getProgressKey(),
                'tenant_id' => $result['tenant']->id,
                'redirect_url' => route('central.tenants.show', $result['tenant']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'progress_key' => $service->getProgressKey(),
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get tenant creation progress (AJAX endpoint).
     */
    public function getProgress(Request $request)
    {
        $progressKey = $request->input('progress_key');

        if (!$progressKey) {
            return response()->json(['error' => 'Progress key required'], 400);
        }

        $service = new TenantCreationService();
        $service = new TenantCreationService(str_replace('tenant_creation_progress_', '', $progressKey));

        return response()->json($service->getProgress());
    }

    /**
     * Calculate subscription end date based on billing interval.
     */
    private function calculateSubscriptionEndDate(string $billingInterval): \Carbon\Carbon
    {
        return match ($billingInterval) {
            'week' => now()->addWeek(),
            'month' => now()->addMonth(),
            'two_month' => now()->addMonths(2),
            'quarter' => now()->addMonths(3),
            'six_month' => now()->addMonths(6),
            'year' => now()->addYear(),
            default => now()->addMonth(), // Default to monthly
        };
    }
}
