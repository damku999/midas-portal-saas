<?php

namespace App\Modules\Policy\Services;

use App\Events\Insurance\PolicyCreated;
use App\Events\Insurance\PolicyExpiringWarning;
use App\Events\Insurance\PolicyRenewed;
use App\Models\CustomerInsurance;
use App\Modules\Policy\Contracts\PolicyServiceInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PolicyService implements PolicyServiceInterface
{
    public function getPolicies(Request $request): LengthAwarePaginator
    {
        $query = CustomerInsurance::with(['customer', 'insuranceCompany']);

        // Apply filters
        if ($search = $request->input('search')) {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            })->orWhere('policy_number', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($customerId = $request->input('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        if ($companyId = $request->input('insurance_company_id')) {
            $query->where('insurance_company_id', $companyId);
        }

        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        // Apply sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($request->input('per_page', 15));
    }

    public function createPolicy(array $policyData): CustomerInsurance
    {
        DB::beginTransaction();

        try {
            // Generate policy number if not provided
            if (! isset($policyData['policy_number'])) {
                $policyData['policy_number'] = $this->generatePolicyNumber();
            }

            // Calculate end date if not provided
            if (! isset($policyData['end_date']) && isset($policyData['start_date'])) {
                $startDate = Carbon::parse($policyData['start_date']);
                $policyData['end_date'] = $startDate->addYear()->toDateString();
            }

            $policy = CustomerInsurance::create($policyData);

            DB::commit();

            // Fire PolicyCreated event
            PolicyCreated::dispatch($policy, auth()->user()->name ?? 'system');

            return $policy;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updatePolicy(int $policyId, array $updateData): bool
    {
        DB::beginTransaction();

        try {
            $policy = CustomerInsurance::findOrFail($policyId);
            $originalData = $policy->toArray();

            $updated = $policy->update($updateData);

            if ($updated) {
                DB::commit();

                // Log policy update
                Log::info('Policy updated', [
                    'policy_id' => $policyId,
                    'changes' => array_diff_assoc($updateData, $originalData),
                    'updated_by' => auth()->user()->name ?? 'system',
                ]);

                return true;
            }

            DB::rollBack();

            return false;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function renewPolicy(int $policyId, array $renewalData): CustomerInsurance
    {
        DB::beginTransaction();

        try {
            $originalPolicy = CustomerInsurance::findOrFail($policyId);

            // Create renewal policy data
            $renewalPolicyData = [
                'customer_id' => $originalPolicy->customer_id,
                'insurance_company_id' => $renewalData['insurance_company_id'] ?? $originalPolicy->insurance_company_id,
                'policy_number' => $this->generateRenewalPolicyNumber($originalPolicy->policy_number),
                'policy_type' => $renewalData['policy_type'] ?? $originalPolicy->policy_type,
                'vehicle_number' => $originalPolicy->vehicle_number,
                'make_model_variant' => $originalPolicy->make_model_variant,
                'manufacturing_year' => $originalPolicy->manufacturing_year,
                'fuel_type' => $originalPolicy->fuel_type,
                'engine_number' => $originalPolicy->engine_number,
                'chassis_number' => $originalPolicy->chassis_number,
                'registration_date' => $originalPolicy->registration_date,

                // Premium and coverage details
                'sum_assured' => $renewalData['sum_assured'] ?? $originalPolicy->sum_assured,
                'premium' => $renewalData['premium'] ?? $originalPolicy->premium,
                'addon_covers' => $renewalData['addon_covers'] ?? $originalPolicy->addon_covers,

                // Renewal specific dates
                'start_date' => $renewalData['start_date'] ?? Carbon::parse($originalPolicy->end_date)->toDateString(),
                'end_date' => $renewalData['end_date'] ?? Carbon::parse($originalPolicy->end_date)->addYear()->toDateString(),

                // Status and tracking
                'status' => 'Active',
                'is_renewal' => true,
                'parent_policy_id' => $originalPolicy->id,
                'commission_percentage' => $renewalData['commission_percentage'] ?? $originalPolicy->commission_percentage,
            ];

            // Create new renewal policy
            $renewalPolicy = CustomerInsurance::create($renewalPolicyData);

            // Update original policy status
            $originalPolicy->update([
                'status' => 'Renewed',
                'renewed_policy_id' => $renewalPolicy->id,
                'renewal_date' => now(),
            ]);

            DB::commit();

            // Fire PolicyRenewed event
            PolicyRenewed::dispatch($renewalPolicy, $originalPolicy);

            return $renewalPolicy;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function cancelPolicy(int $policyId, string $reason): bool
    {
        DB::beginTransaction();

        try {
            $policy = CustomerInsurance::findOrFail($policyId);

            $updated = $policy->update([
                'status' => 'Cancelled',
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
                'cancelled_by' => auth()->user()->id ?? null,
            ]);

            if ($updated) {
                DB::commit();

                Log::info('Policy cancelled', [
                    'policy_id' => $policyId,
                    'policy_number' => $policy->policy_number,
                    'reason' => $reason,
                    'cancelled_by' => auth()->user()->name ?? 'system',
                ]);

                return true;
            }

            DB::rollBack();

            return false;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getPolicyById(int $id): ?CustomerInsurance
    {
        return CustomerInsurance::with([
            'customer',
            'insuranceCompany',
            'parentPolicy',
            'renewedPolicy',
        ])->find($id);
    }

    public function getActivePolicies(): Collection
    {
        return CustomerInsurance::with(['customer', 'insuranceCompany'])
            ->where('status', 'Active')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getExpiringPolicies(int $daysAhead = 30): Collection
    {
        $expiringDate = Carbon::now()->addDays($daysAhead);

        $policies = CustomerInsurance::with(['customer', 'insuranceCompany'])
            ->where('status', 'Active')
            ->whereDate('end_date', '<=', $expiringDate)
            ->whereDate('end_date', '>=', Carbon::now())
            ->orderBy('end_date')
            ->get();

        // Fire expiring warning events for policies expiring in 30 days or less
        foreach ($policies as $policy) {
            $daysUntilExpiry = Carbon::now()->diffInDays(Carbon::parse($policy->end_date));

            if (in_array($daysUntilExpiry, [30, 15, 7, 3, 1])) {
                PolicyExpiringWarning::dispatch($policy, $daysUntilExpiry);
            }
        }

        return $policies;
    }

    public function calculatePremium(array $policyData): float
    {
        // Basic premium calculation logic
        $baseIdv = $policyData['sum_assured'] ?? 0;
        $vehicleAge = date('Y') - ($policyData['manufacturing_year'] ?? date('Y'));

        // Base rate calculation
        $baseRate = match (true) {
            $vehicleAge <= 1 => 0.025, // 2.5%
            $vehicleAge <= 3 => 0.030, // 3.0%
            $vehicleAge <= 5 => 0.035, // 3.5%
            default => 0.040, // 4.0%
        };

        $basePremium = $baseIdv * $baseRate;

        // Add-on covers
        $addonPremium = 0;
        $addons = $policyData['addon_covers'] ?? [];

        foreach ($addons as $addon) {
            $addonPremium += match ($addon) {
                'Zero Depreciation' => $baseIdv * 0.004,
                'Engine Protection' => $baseIdv * 0.001,
                'Road Side Assistance' => 180,
                'NCB Protection' => $baseIdv * 0.0005,
                'Personal Accident' => 450,
                default => 0,
            };
        }

        // Taxes (18% GST)
        $netPremium = $basePremium + $addonPremium;
        $gst = $netPremium * 0.18;

        return round($netPremium + $gst, 2);
    }

    public function calculateCommission(CustomerInsurance $policy): float
    {
        $premium = $policy->premium;
        $commissionRate = $policy->commission_percentage ?? 10; // Default 10%

        return round(($premium * $commissionRate) / 100, 2);
    }

    public function getPolicyStatistics(): array
    {
        return [
            'total' => CustomerInsurance::count(),
            'active' => CustomerInsurance::where('status', 'Active')->count(),
            'expired' => CustomerInsurance::where('status', 'Expired')->count(),
            'cancelled' => CustomerInsurance::where('status', 'Cancelled')->count(),
            'renewed' => CustomerInsurance::where('status', 'Renewed')->count(),
            'expiring_soon' => CustomerInsurance::where('status', 'Active')
                ->whereDate('end_date', '<=', Carbon::now()->addDays(30))
                ->whereDate('end_date', '>=', Carbon::now())
                ->count(),
            'total_premium' => CustomerInsurance::where('status', 'Active')->sum('premium'),
            'total_commission' => CustomerInsurance::where('status', 'Active')
                ->get()
                ->sum(function ($policy) {
                    return $this->calculateCommission($policy);
                }),
        ];
    }

    public function searchPolicies(string $query): Collection
    {
        return CustomerInsurance::with(['customer', 'insuranceCompany'])
            ->where('policy_number', 'like', "%{$query}%")
            ->orWhereHas('customer', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('mobile_number', 'like', "%{$query}%");
            })
            ->orWhere('vehicle_number', 'like', "%{$query}%")
            ->orWhere('make_model_variant', 'like', "%{$query}%")
            ->limit(50)
            ->get();
    }

    public function getCustomerPolicies(int $customerId): Collection
    {
        return CustomerInsurance::with(['insuranceCompany'])
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Generate a unique policy number.
     */
    private function generatePolicyNumber(): string
    {
        $year = date('Y');
        $month = date('m');

        // Get the last policy number for this month
        $lastPolicy = CustomerInsurance::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastPolicy && $lastPolicy->policy_number) {
            // Extract sequence from last policy number (format: POL/YY/MM/NNNN)
            $parts = explode('/', $lastPolicy->policy_number);
            if (count($parts) === 4) {
                $sequence = intval($parts[3]) + 1;
            }
        }

        return sprintf('POL/%02d/%02d/%04d', $year % 100, $month, $sequence);
    }

    /**
     * Generate renewal policy number.
     */
    private function generateRenewalPolicyNumber(string $originalPolicyNumber): string
    {
        $year = date('Y');
        $renewalSuffix = 'R'.($year % 100);

        return $originalPolicyNumber.'/'.$renewalSuffix;
    }
}
