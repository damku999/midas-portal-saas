<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\AuditLog;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
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
            'subdomain' => 'required|string|alpha_dash|max:63|unique:domains,domain',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'plan_id' => 'required|exists:plans,id',
            'trial_enabled' => 'boolean',
            'trial_days' => 'nullable|integer|min:1|max:90',
            'admin_first_name' => 'required|string|max:255',
            'admin_last_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'nullable|string|min:8',
            'send_welcome_email' => 'boolean',
        ]);

        DB::connection('central')->beginTransaction();

        try {
            // Create tenant
            $tenant = Tenant::create([
                'id' => Str::uuid()->toString(),
            ]);

            // Create domain
            $domain = $validated['subdomain'].'.'.config('app.domain', 'midastech.in');
            $tenant->domains()->create([
                'domain' => $domain,
            ]);

            // Store company data in tenant data
            $tenant->update([
                'data' => [
                    'company_name' => $validated['company_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                ],
            ]);

            // Create subscription
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

            // Run tenant migrations and seed default data
            $tenant->run(function ($tenant) use ($validated) {
                // Create admin user in tenant database
                $password = $validated['admin_password'] ?? Str::random(16);

                DB::table('users')->insert([
                    'name' => $validated['admin_first_name'].' '.$validated['admin_last_name'],
                    'email' => $validated['admin_email'],
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Seed default tenant data
                \Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\Tenant\\DefaultTenantSeeder',
                    '--force' => true,
                ]);

                // Store password in tenant data for welcome email
                $tenant->update([
                    'data' => array_merge($tenant->data ?? [], [
                        'admin_password' => $password,
                    ]),
                ]);
            });

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
            DB::connection('central')->rollBack();

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
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'status' => ['required', Rule::in(['trial', 'active', 'suspended', 'cancelled'])],
        ]);

        $tenant->update([
            'data' => array_merge($tenant->data ?? [], [
                'company_name' => $validated['company_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ]),
        ]);

        // Update subscription status if changed
        if ($tenant->subscription && $tenant->subscription->status !== $validated['status']) {
            $tenant->subscription->update(['status' => $validated['status']]);

            AuditLog::log(
                'tenant.status_changed',
                "Changed tenant status to: {$validated['status']}",
                auth('central')->user(),
                $tenant->id,
                ['old_status' => $tenant->subscription->status, 'new_status' => $validated['status']]
            );
        }

        return redirect()
            ->route('central.tenants.show', $tenant)
            ->with('success', 'Tenant updated successfully!');
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

            AuditLog::log(
                'tenant.suspended',
                "Suspended tenant: {$tenant->data['company_name']}",
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

            AuditLog::log(
                'tenant.activated',
                "Activated tenant: {$tenant->data['company_name']}",
                auth('central')->user(),
                $tenant->id
            );
        }

        return redirect()
            ->route('central.tenants.show', $tenant)
            ->with('success', 'Tenant activated successfully!');
    }

    /**
     * Remove the specified tenant.
     */
    public function destroy(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'confirmation' => 'required|string',
        ]);

        $companyName = $tenant->data['company_name'] ?? '';
        $expectedConfirmation = 'DELETE '.strtoupper($companyName);

        if ($validated['confirmation'] !== $expectedConfirmation) {
            return back()->with('error', 'Confirmation text does not match. Tenant not deleted.');
        }

        DB::connection('central')->beginTransaction();

        try {
            // Log deletion before deleting
            AuditLog::log(
                'tenant.deleted',
                "Deleted tenant: {$companyName}",
                auth('central')->user(),
                $tenant->id,
                ['company_name' => $companyName]
            );

            // Soft delete tenant (actual database deletion handled by tenancy package)
            $tenant->delete();

            DB::connection('central')->commit();

            return redirect()
                ->route('central.tenants.index')
                ->with('success', 'Tenant deleted successfully!');

        } catch (\Exception $e) {
            DB::connection('central')->rollBack();

            return back()->with('error', 'Failed to delete tenant: '.$e->getMessage());
        }
    }
}
