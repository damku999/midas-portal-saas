<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\AuditLog;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;

class DashboardController extends Controller
{
    /**
     * Display the central admin dashboard.
     */
    public function index()
    {
        // Get metrics
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::active()->count();
        $trialTenants = Tenant::onTrial()->count();
        $suspendedTenants = Tenant::suspended()->count();

        // Calculate MRR (Monthly Recurring Revenue)
        $mrr = Subscription::active()->sum('mrr');

        // Get new tenants this month
        $newTenantsThisMonth = Tenant::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Get recent tenants
        $recentTenants = Tenant::with(['subscription.plan'])
            ->latest()
            ->limit(10)
            ->get();

        // Get recent activity
        $recentActivity = AuditLog::with(['tenantUser', 'tenant'])
            ->latest()
            ->limit(10)
            ->get();

        // Get tenant status distribution
        $statusDistribution = [
            'active' => $activeTenants,
            'trial' => $trialTenants,
            'suspended' => $suspendedTenants,
            'cancelled' => Subscription::cancelled()->count(),
        ];

        // Get revenue by plan
        $revenueByPlan = Plan::withCount('subscriptions')
            ->with(['subscriptions' => function ($query) {
                $query->active();
            }])
            ->get()
            ->map(function ($plan) {
                return [
                    'name' => $plan->name,
                    'revenue' => $plan->subscriptions->sum('mrr'),
                    'count' => $plan->subscriptions->count(),
                ];
            });

        return view('central.dashboard', compact(
            'totalTenants',
            'activeTenants',
            'trialTenants',
            'suspendedTenants',
            'mrr',
            'newTenantsThisMonth',
            'recentTenants',
            'recentActivity',
            'statusDistribution',
            'revenueByPlan'
        ));
    }
}
