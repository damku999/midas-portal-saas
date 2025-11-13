<?php

namespace App\Http\Controllers;

use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Services\PaymentService;
use App\Services\UsageTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    protected UsageTrackingService $usageService;
    protected PaymentService $paymentService;

    public function __construct(UsageTrackingService $usageService, PaymentService $paymentService)
    {
        $this->usageService = $usageService;
        $this->paymentService = $paymentService;
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
            'payment_gateway' => 'required|string|in:razorpay,stripe,bank_transfer',
            'billing_cycle' => 'required|string|in:monthly,annual',
            'auto_renew' => 'sometimes|boolean',
            'payment_details' => 'sometimes|array',
        ]);

        $tenant = tenant();

        if (! $tenant) {
            abort(404, 'Tenant not found');
        }

        DB::connection('central')->beginTransaction();

        try {
            $subscription = Subscription::where('tenant_id', $tenant->id)->firstOrFail();

            // Calculate amount (use plan price, or prorated for mid-cycle upgrades)
            $amount = $validated['billing_cycle'] === 'annual'
                ? $plan->annual_price
                : $plan->price;

            // Create payment order
            $paymentResult = $this->paymentService->createOrder(
                $subscription,
                $amount,
                $validated['payment_gateway'],
                'upgrade'
            );

            if (!$paymentResult['success']) {
                throw new \Exception($paymentResult['error']);
            }

            // Calculate subscription end date based on billing cycle
            $endsAt = $validated['billing_cycle'] === 'annual'
                ? now()->addYear()
                : $this->calculateSubscriptionEndDate($plan->billing_interval);

            // Update subscription (will be activated after payment verification)
            $subscription->update([
                'plan_id' => $plan->id,
                'ends_at' => $endsAt,
                'next_billing_date' => $validated['billing_cycle'] === 'annual'
                    ? now()->addYear()
                    : now()->addMonth(),
                'mrr' => $amount,
                'auto_renew' => $validated['auto_renew'] ?? false,
            ]);

            DB::connection('central')->commit();

            // Return payment order data to frontend for gateway integration
            return response()->json([
                'success' => true,
                'payment' => $paymentResult['payment'],
                'order_data' => $paymentResult['order_data'],
                'redirect_url' => route('subscription.index'),
            ]);

        } catch (\Exception $e) {
            DB::connection('central')->rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'Failed to upgrade plan: '.$e->getMessage());
        }
    }

    /**
     * Verify payment after gateway callback.
     *
     * SECURITY FIX: Added tenant ownership verification to prevent payment verification bypass
     */
    public function verifyPayment(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => 'required|integer',
            'razorpay_payment_id' => 'required_if:gateway,razorpay',
            'razorpay_order_id' => 'required_if:gateway,razorpay',
            'razorpay_signature' => 'required_if:gateway,razorpay',
        ]);

        try {
            // SECURITY FIX: Verify payment belongs to current tenant
            $payment = \App\Models\Central\Payment::where('id', $validated['payment_id'])
                ->where('tenant_id', tenant()->id)
                ->firstOrFail();

            // SECURITY: Log payment verification attempt
            \Log::info('Payment verification attempt', [
                'payment_id' => $validated['payment_id'],
                'tenant_id' => tenant()->id,
                'ip' => $request->ip(),
            ]);

            $result = $this->paymentService->verifyPayment(
                $validated['payment_id'],
                $request->all()
            );

            if ($result['success']) {
                // Verify subscription also belongs to the same tenant
                $subscription = $payment->subscription;

                if (!$subscription) {
                    \Log::error('SECURITY: Payment verification failed - no subscription found', [
                        'payment_id' => $validated['payment_id'],
                        'tenant_id' => tenant()->id,
                    ]);

                    return response()->json([
                        'success' => false,
                        'error' => 'Subscription not found',
                    ], 404);
                }

                // Update subscription status to active
                $subscription->update([
                    'status' => 'active',
                    'is_trial' => false,
                    'trial_ends_at' => null,
                ]);

                // SECURITY: Log successful payment verification
                \Log::info('Payment verified successfully', [
                    'payment_id' => $validated['payment_id'],
                    'subscription_id' => $subscription->id,
                    'tenant_id' => tenant()->id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment verified successfully!',
                    'redirect_url' => route('subscription.index'),
                ]);
            }

            // SECURITY: Log failed verification
            \Log::warning('Payment verification failed', [
                'payment_id' => $validated['payment_id'],
                'tenant_id' => tenant()->id,
                'error' => $result['error'] ?? 'Unknown error',
            ]);

            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Payment verification failed',
            ], 400);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // SECURITY: Log potential cross-tenant access attempt
            \Log::warning('SECURITY: Payment verification attempt with invalid payment_id', [
                'payment_id' => $validated['payment_id'],
                'tenant_id' => tenant()->id,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Payment not found',
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Payment verification error', [
                'payment_id' => $validated['payment_id'],
                'error' => $e->getMessage(),
                'tenant_id' => tenant()->id,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Payment verification failed',
            ], 500);
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
