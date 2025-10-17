<?php

namespace App\Services;

use App\Contracts\Services\ReportServiceInterface;
use App\Exports\CrossSellingExport;
use App\Exports\CustomerInsurancesExport1;
use App\Models\Broker;
use App\Models\Customer;
use App\Models\FuelType;
use App\Models\InsuranceCompany;
use App\Models\PolicyType;
use App\Models\PremiumType;
use App\Models\RelationshipManager;
use App\Models\Report;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportService implements ReportServiceInterface
{
    /**
     * Get initial data for reports page with comprehensive filter options
     */
    public function getInitialData(): array
    {
        return [
            'brokers' => Broker::query()->select('id', 'name')->orderBy('name')->get(),
            'relationship_managers' => RelationshipManager::query()->select('id', 'name')->orderBy('name')->get(),
            'insurance_companies' => InsuranceCompany::query()->select('id', 'name')->orderBy('name')->get(),
            'policy_types' => PolicyType::query()->select('id', 'name')->orderBy('name')->get(),
            'fuel_types' => FuelType::query()->select('id', 'name')->orderBy('name')->get(),
            'premium_types' => PremiumType::query()->select('id', 'name')->orderBy('name')->get(),
            'customers' => Customer::query()->select('id', 'name')->orderBy('name')->get(),
            'customerInsurances' => [],
            'crossSelling' => [],
        ];
    }

    /**
     * Generate cross selling report with analysis - using same logic as Excel export
     */
    public function generateCrossSellingReport(array $parameters): array
    {
        $premiumTypes = PremiumType::query()->select('id', 'name');
        if (! empty($parameters['premium_type_id'])) {
            $premiumTypes = $premiumTypes->whereIn('id', $parameters['premium_type_id']);
        }

        $premiumTypes = $premiumTypes->get();

        $customer_obj = Customer::with(['insurance.premiumType', 'insurance.broker', 'insurance.relationshipManager', 'insurance.insuranceCompany'])->orderBy('name');
        $hasDateFilter = false;

        // Apply comprehensive filters matching Excel export exactly
        if (! empty($parameters['issue_start_date']) || ! empty($parameters['issue_end_date'])) {
            $customer_obj = $customer_obj->whereHas('insurance', static function ($query) use ($parameters): void {
                if (! empty($parameters['issue_start_date'])) {
                    try {
                        $startDate = Carbon::createFromFormat('d/m/Y', $parameters['issue_start_date'])->format('Y-m-d');
                        $query->where('start_date', '>=', $startDate);
                    } catch (\Exception) {
                        $query->where('start_date', '>=', $parameters['issue_start_date']);
                    }
                }

                if (! empty($parameters['issue_end_date'])) {
                    try {
                        $endDate = Carbon::createFromFormat('d/m/Y', $parameters['issue_end_date'])->format('Y-m-d');
                        $query->where('start_date', '<=', $endDate);
                    } catch (\Exception) {
                        $query->where('start_date', '<=', $parameters['issue_end_date']);
                    }
                }
            });
            $hasDateFilter = true;
        }

        // Business entity filters
        if (! empty($parameters['broker_id'])) {
            $customer_obj = $customer_obj->whereHas('insurance', static function ($query) use ($parameters): void {
                $query->where('broker_id', $parameters['broker_id']);
            });
        }

        if (! empty($parameters['relationship_manager_id'])) {
            $customer_obj = $customer_obj->whereHas('insurance', static function ($query) use ($parameters): void {
                $query->where('relationship_manager_id', $parameters['relationship_manager_id']);
            });
        }

        if (! empty($parameters['insurance_company_id'])) {
            $customer_obj = $customer_obj->whereHas('insurance', static function ($query) use ($parameters): void {
                $query->where('insurance_company_id', $parameters['insurance_company_id']);
            });
        }

        if (! empty($parameters['customer_id'])) {
            $customer_obj = $customer_obj->where('id', $parameters['customer_id']);
        }

        $customers = $customer_obj->get();
        $oneYearAgo = Carbon::now()->subYear();

        $results = $customers->map(static function ($customer) use ($premiumTypes, $oneYearAgo, $hasDateFilter): array {
            $customerData = ['Customer Name' => $customer->name];

            if (! $hasDateFilter) {
                $customerData['Total Premium (Last Year)'] = number_format($customer->insurance
                    ->where('start_date', '>=', $oneYearAgo)
                    ->sum('final_premium_with_gst'), 2);
                $customerData['Actual Earnings (Last Year)'] = number_format($customer->insurance
                    ->where('start_date', '>=', $oneYearAgo)
                    ->sum('actual_earnings'), 2);
            } else {
                $customerData['Total Premium (Last Year)'] = number_format($customer->insurance
                    ->sum('final_premium_with_gst'), 2);
                $customerData['Actual Earnings (Last Year)'] = number_format($customer->insurance
                    ->sum('actual_earnings'), 2);
            }

            foreach ($premiumTypes as $premiumType) {
                $hasPremiumType = $customer->insurance->contains(fn ($insurance): bool => $insurance->premiumType->id === $premiumType->id);

                $premiumTotal = $customer->insurance
                    ->where('premium_type_id', $premiumType->id)
                    ->sum('final_premium_with_gst');

                // Flatten the data for table display
                $customerData[$premiumType->name] = $hasPremiumType ? 'Yes' : 'No';
                $customerData[$premiumType->name.' (Sum Insured)'] = '₹'.number_format($premiumTotal, 2);
            }

            return $customerData;
        });

        return [
            'premiumTypes' => $premiumTypes,
            'crossSelling' => $results,
        ];
    }

    /**
     * Generate customer insurance report
     */
    public function generateCustomerInsuranceReport(array $parameters): array
    {
        $result = Report::getInsuranceReport($parameters);

        if (! $result || $result->isEmpty()) {
            return [];
        }

        // Convert the collection to array format expected by the view
        $reportData = $result->map(fn ($customerInsurance): array => [
            'customer_name' => $customerInsurance->customer->name ?? 'N/A',
            'policy_number' => $customerInsurance->policy_no ?? 'N/A',
            'insurance_company' => $customerInsurance->insuranceCompany->name ?? 'N/A',
            'issue_date' => $customerInsurance->issue_date ?? 'N/A',
            'expired_date' => $customerInsurance->expired_date ?? 'N/A',
            'premium_amount' => $customerInsurance->final_premium_with_gst ?? 0,
            'status' => $customerInsurance->status ?? 0,
            // Additional fields that might be needed for comprehensive analysis
            'branch' => $customerInsurance->branch->name ?? 'N/A',
            'broker' => $customerInsurance->broker->name ?? 'N/A',
            'relationship_manager' => $customerInsurance->relationshipManager->name ?? 'N/A',
            'premium_type' => $customerInsurance->premiumType->name ?? 'N/A',
            'policy_type' => $customerInsurance->policyType->name ?? 'N/A',
            'start_date' => $customerInsurance->start_date ?? 'N/A',
            'actual_earnings' => $customerInsurance->actual_earnings ?? 0,
            'commission_on' => $customerInsurance->commission_on ?? 0,
        ]);

        // Apply intelligent sorting for due policy reports
        if (isset($parameters['report_name']) && $parameters['report_name'] === 'due_policy_detail') {
            $reportData = $reportData->sort(static function (array $a, array $b): int {
                $dateA = $a['expired_date'] ?? null;
                $dateB = $b['expired_date'] ?? null;

                // Handle cases where dates might be null or 'N/A'
                if (! $dateA || $dateA === 'N/A') {
                    return 1;
                }
                // Put at end
                if (! $dateB || $dateB === 'N/A') {
                    return -1;
                } // Put at end

                $expiryA = Carbon::parse($dateA);
                $expiryB = Carbon::parse($dateB);
                $now = Carbon::now();

                $isExpiredA = $expiryA->isPast();
                $isExpiredB = $expiryB->isPast();

                // Priority 1: Expired policies first
                if ($isExpiredA && ! $isExpiredB) {
                    return -1;
                }
                // A is expired, B is not - A comes first
                if (! $isExpiredA && $isExpiredB) {
                    return 1;
                }  // B is expired, A is not - B comes first

                // Priority 2: Among expired policies, oldest expiry first (EXPIRED 42 days ago before EXPIRED 8 days ago)
                // Priority 3: Among active policies, soonest expiry first (8 days remaining before 16 days remaining)
                // Both cases: DUE DATE ASC
                return $expiryA->timestamp <=> $expiryB->timestamp; // Always ascending by expiry date
            });
        }

        return $reportData->values()->toArray(); // Re-index array after sorting
    }

    /**
     * Export cross selling report to Excel
     */
    public function exportCrossSellingReport(array $parameters): BinaryFileResponse
    {
        $timestamp = date('Y-m-d_H-i-s');

        return Excel::download(new CrossSellingExport($parameters), sprintf('cross_selling_report_%s.xlsx', $timestamp));
    }

    /**
     * Export customer insurance report to Excel
     */
    public function exportCustomerInsuranceReport(array $parameters): BinaryFileResponse
    {
        // Generate filename based on report type
        $reportName = $parameters['report_name'] ?? 'customer_insurances';
        $timestamp = date('Y-m-d_H-i-s');

        $filename = match ($reportName) {
            'insurance_detail' => sprintf('insurance_detail_report_%s.xlsx', $timestamp),
            'due_policy_detail' => sprintf('due_policy_report_%s.xlsx', $timestamp),
            default => sprintf('customer_insurances_%s.xlsx', $timestamp)
        };

        return Excel::download(new CustomerInsurancesExport1($parameters), $filename);
    }

    /**
     * Save user's selected columns for a report
     */
    public function saveUserReportColumns(string $reportName, array $selectedColumns, int $userId): void
    {
        $updatedColumns = [];
        foreach (config('constants.INSURANCE_DETAIL') as $column) {
            $selected = in_array($column['table_column_name'], $selectedColumns) ? 'Yes' : 'No';
            $column['selected_column'] = $selected;
            $updatedColumns[] = $column;
        }

        Report::query()->updateOrCreate([
            'name' => $reportName,
            'user_id' => $userId,
        ], [
            'name' => $reportName,
            'user_id' => $userId,
            'selected_columns' => $updatedColumns,
        ]);
    }

    /**
     * Load user's saved columns for a report
     */
    public function loadUserReportColumns(string $reportName, int $userId): ?array
    {
        $report = Report::query()->where([
            'name' => $reportName,
            'user_id' => $userId,
        ])->first();

        return $report ? $report->selected_columns : null;
    }
}
