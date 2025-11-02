<?php

namespace App\Http\Controllers;

use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Services\UsageTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    protected UsageTrackingService $usageService;

    public function __construct(UsageTrackingService $usageService)
    {
        $this->usageService = $usageService;
    }

    /**
     * Display subscription and usage information.
     */
    public function index()
    {
        $tenant = tenant();

        if (! $tenant) {
            abort(404, 'Tenant not found');
        }

        $subscription = Subscription::where('tenant_id', $tenant->id)
            ->with('plan')
            ->firstOrFail();

        $usageSummary = $this->usageService->getUsageSummary($tenant);

        return view('subscription.index', compact('subscription', 'usageSummary'));
    }

    /**
     * Show available plans for upgrade.
     */
    public function plans()
    {
        $tenant = tenant();

        if (! $tenant) {
            abort(404, 'Tenant not found');
        }

        $currentSubscription = Subscription::where('tenant_id', $tenant->id)
            ->with('plan')
            ->firstOrFail();

        $plans = Plan::active()->ordered()->get();
        $usageSummary = $this->usageService->getUsageSummary($tenant);

        return view('subscription.plans', compact('currentSubscription', 'plans', 'usageSummary'));
    }

    /**
     * Show upgrade confirmation page.
     */
    public function upgrade(Plan $plan)
    {
        $tenant = tenant();

        if (! $tenant) {
            abort(404, 'Tenant not found');
        }

        $currentSubscription = Subscription::where('tenant_id', $tenant->id)
            ->with('plan')
            ->firstOrFail();

        // Prevent downgrade to same or lower plan
        if ($plan->price <= $currentSubscription->plan->price) {
            return redirect()->route('subscription.plans')
                ->with('error', 'Please select a higher plan to upgrade.');
        }

        $usageSummary = $this->usageService->getUsageSummary($tenant);

        return view('subscription.upgrade', compact('currentSubscription', 'plan', 'usageSummary'));
    }

    /**
     * Process plan upgrade.
     */
    public function processUpgrade(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string|in:razorpay,stripe,bank_transfer',
            'billing_cycle' => 'required|string|in:monthly,annual',
        ]);

        $tenant = tenant();

        if (! $tenant) {
            abort(404, 'Tenant not found');
        }

        DB::connection('central')->beginTransaction();

        try {
            $subscription = Subscription::where('tenant_id', $tenant->id)->firstOrFail();

            // Calculate prorated amount if applicable
            $proratedAmount = $this->calculateProratedAmount($subscription, $plan);

            // Update subscription
            $subscription->update([
                'plan_id' => $plan->id,
                'status' => 'active',
                'is_trial' => false,
                'trial_ends_at' => null,
                'next_billing_date' => $validated['billing_cycle'] === 'annual'
                    ? now()->addYear()
                    : now()->addMonth(),
                'mrr' => $validated['billing_cycle'] === 'annual'
                    ? $plan->price
                    : $plan->price,
            ]);

            DB::connection('central')->commit();

            // TODO: Process actual payment with payment gateway
            // $paymentResult = $this->processPayment($validated['payment_method'], $proratedAmount);

            return redirect()->route('subscription.index')
                ->with('success', 'Plan upgraded successfully! Your new features are now available.');

        } catch (\Exception $e) {
            DB::connection('central')->rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to upgrade plan: ' . $e->getMessage());
        }
    }

    /**
     * Show subscription required page.
     */
    public function required()
    {
        return view('subscription.required');
    }

    /**
     * Show subscription suspended page.
     */
    public function suspended()
    {
        return view('subscription.suspended');
    }

    /**
     * Show subscription cancelled page.
     */
    public function cancelled()
    {
        return view('subscription.cancelled');
    }

    /**
     * Show usage details page.
     */
    public function usage()
    {
        $tenant = tenant();

        if (! $tenant) {
            abort(404, 'Tenant not found');
        }

        $subscription = Subscription::where('tenant_id', $tenant->id)
            ->with('plan')
            ->firstOrFail();

        $usageSummary = $this->usageService->getUsageSummary($tenant);

        return view('subscription.usage', compact('subscription', 'usageSummary'));
    }

    /**
     * Calculate prorated amount for plan upgrade.
     */
    private function calculateProratedAmount(Subscription $currentSubscription, Plan $newPlan): float
    {
        $currentPlan = $currentSubscription->plan;
        $daysRemaining = now()->diffInDays($currentSubscription->next_billing_date);
        $totalDays = 30; // Assuming monthly billing

        $unusedAmount = ($currentPlan->price / $totalDays) * $daysRemaining;
        $newAmount = ($newPlan->price / $totalDays) * $daysRemaining;

        return max(0, $newAmount - $unusedAmount);
    }
}
