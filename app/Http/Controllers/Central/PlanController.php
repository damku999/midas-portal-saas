<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\AuditLog;
use App\Models\Central\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    /**
     * Display a listing of plans.
     */
    public function index(Request $request)
    {
        $query = Plan::withCount('subscriptions');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Sort
        $sortField = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $plans = $query->paginate(20);

        return view('central.plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new plan.
     */
    public function create()
    {
        return view('central.plans.create');
    }

    /**
     * Store a newly created plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:125',
            'slug' => [
                'required',
                'string',
                'max:125',
                'alpha_dash',
                'unique:plans,slug',
            ],
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_interval' => ['required', Rule::in(['week', 'month', 'two_month', 'quarter', 'six_month', 'year'])],
            'max_users' => 'required|integer|min:-1',
            'max_customers' => 'required|integer|min:-1',
            'max_leads_per_month' => 'required|integer|min:-1',
            'storage_limit_gb' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $plan = Plan::create($validated);

        AuditLog::log(
            'plan.created',
            "Created plan: {$plan->name}",
            auth('central')->user(),
            null,
            ['plan_id' => $plan->id, 'plan_name' => $plan->name]
        );

        return redirect()
            ->route('central.plans.index')
            ->with('success', 'Plan created successfully!');
    }

    /**
     * Display the specified plan.
     */
    public function show(Plan $plan)
    {
        $plan->loadCount('subscriptions');
        $plan->load(['subscriptions' => function ($query) {
            $query->with('tenant.domains')->latest()->limit(10);
        }]);

        return view('central.plans.show', compact('plan'));
    }

    /**
     * Show the form for editing the specified plan.
     */
    public function edit(Plan $plan)
    {
        $plan->loadCount('subscriptions');

        return view('central.plans.edit', compact('plan'));
    }

    /**
     * Update the specified plan.
     */
    public function update(Request $request, Plan $plan)
    {
        // Check if plan has active subscriptions
        $hasSubscriptions = $plan->subscriptions()->count() > 0;

        $rules = [
            'name' => 'required|string|max:125',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];

        // Only allow certain fields to be updated if plan has no subscriptions
        if (!$hasSubscriptions) {
            $rules['slug'] = [
                'required',
                'string',
                'max:125',
                'alpha_dash',
                Rule::unique('plans', 'slug')->ignore($plan->id),
            ];
            $rules['price'] = 'required|numeric|min:0';
            $rules['billing_interval'] = ['required', Rule::in(['week', 'month', 'two_month', 'quarter', 'six_month', 'year'])];
            $rules['max_users'] = 'required|integer|min:-1';
            $rules['max_customers'] = 'required|integer|min:-1';
            $rules['max_leads_per_month'] = 'required|integer|min:-1';
            $rules['storage_limit_gb'] = 'required|integer|min:1';
            $rules['features'] = 'nullable|array';
            $rules['features.*'] = 'string';
        }

        $validated = $request->validate($rules);

        $oldData = $plan->only(['name', 'price', 'billing_interval']);

        $plan->update($validated);

        AuditLog::log(
            'plan.updated',
            "Updated plan: {$plan->name}",
            auth('central')->user(),
            null,
            [
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'has_subscriptions' => $hasSubscriptions,
                'old_data' => $oldData,
                'new_data' => $validated,
            ]
        );

        $message = $hasSubscriptions
            ? 'Plan updated successfully! Note: Some fields are locked because this plan has active subscriptions.'
            : 'Plan updated successfully!';

        return redirect()
            ->route('central.plans.index')
            ->with('success', $message);
    }

    /**
     * Remove the specified plan.
     */
    public function destroy(Plan $plan)
    {
        // Prevent deletion if plan has any subscriptions
        $subscriptionCount = $plan->subscriptions()->count();

        if ($subscriptionCount > 0) {
            return back()->with('error', "Cannot delete plan '{$plan->name}' because it has {$subscriptionCount} active subscription(s). Please reassign tenants to another plan first.");
        }

        $planName = $plan->name;
        $planId = $plan->id;

        $plan->delete();

        AuditLog::log(
            'plan.deleted',
            "Deleted plan: {$planName}",
            auth('central')->user(),
            null,
            ['plan_id' => $planId, 'plan_name' => $planName]
        );

        return redirect()
            ->route('central.plans.index')
            ->with('success', "Plan '{$planName}' deleted successfully!");
    }

    /**
     * Toggle plan active status.
     */
    public function toggleStatus(Plan $plan)
    {
        $newStatus = !$plan->is_active;
        $plan->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'activated' : 'deactivated';

        AuditLog::log(
            'plan.status_changed',
            "Plan {$statusText}: {$plan->name}",
            auth('central')->user(),
            null,
            [
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'new_status' => $newStatus,
            ]
        );

        return back()->with('success', "Plan '{$plan->name}' {$statusText} successfully!");
    }
}
