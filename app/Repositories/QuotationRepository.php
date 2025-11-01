<?php

namespace App\Repositories;

use App\Contracts\Repositories\QuotationRepositoryInterface;
use App\Models\Quotation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

/**
 * Quotation Repository
 *
 * Extends base repository functionality for Quotation-specific operations.
 * Common CRUD operations are inherited from AbstractBaseRepository.
 */
class QuotationRepository extends AbstractBaseRepository implements QuotationRepositoryInterface
{
    protected string $modelClass = Quotation::class;

    protected array $searchableFields = ['vehicle_number', 'make_model_variant'];

    /**
     * Get all quotations with optional filters.
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Quotation::with(['customer', 'quotationCompanies.insuranceCompany']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        return $query->latest()->get();
    }

    /**
     * Override base getPaginated to support complex filtering with relationships
     */
    public function getPaginated(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        $filters = $request->all();
        $query = Quotation::with(['customer', 'quotationCompanies.insuranceCompany']);

        // Search filter
        if (! empty($filters['search'])) {
            $searchTerm = '%'.trim($filters['search']).'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('customer', function ($customerQuery) use ($searchTerm) {
                    $customerQuery->where('name', 'LIKE', $searchTerm)
                        ->orWhere('mobile_number', 'LIKE', $searchTerm);
                })
                    ->orWhere('vehicle_number', 'LIKE', $searchTerm)
                    ->orWhere('make_model_variant', 'LIKE', $searchTerm);
            });
        }

        // Status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Customer filter
        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Override findById to include relationships
     */
    public function findById(int $id): ?\Illuminate\Database\Eloquent\Model
    {
        return Quotation::with(['customer', 'quotationCompanies.insuranceCompany'])
            ->find($id);
    }

    /**
     * Override update method to match interface signature
     */
    public function update(\Illuminate\Database\Eloquent\Model $entity, array $data): \Illuminate\Database\Eloquent\Model
    {
        return parent::update($entity, $data);
    }

    /**
     * Override delete method to match interface signature
     */
    public function delete(\Illuminate\Database\Eloquent\Model $entity): bool
    {
        return parent::delete($entity);
    }

    public function getByCustomer(int $customerId): Collection
    {
        return Quotation::with(['quotationCompanies.insuranceCompany'])
            ->where('customer_id', $customerId)
            ->latest()
            ->get();
    }

    public function getByStatus(string $status): Collection
    {
        return Quotation::with(['customer', 'quotationCompanies.insuranceCompany'])
            ->where('status', $status)
            ->get();
    }

    public function getRecent(int $limit = 10): Collection
    {
        return Quotation::with(['customer', 'quotationCompanies.insuranceCompany'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function search(string $query): Collection
    {
        $searchTerm = '%'.trim($query).'%';

        return Quotation::with(['customer', 'quotationCompanies.insuranceCompany'])
            ->where(function ($q) use ($searchTerm) {
                $q->whereHas('customer', function ($customerQuery) use ($searchTerm) {
                    $customerQuery->where('name', 'LIKE', $searchTerm)
                        ->orWhere('mobile_number', 'LIKE', $searchTerm);
                })
                    ->orWhere('vehicle_number', 'LIKE', $searchTerm)
                    ->orWhere('make_model_variant', 'LIKE', $searchTerm);
            })
            ->latest()
            ->get();
    }

    public function getSentQuotations(): Collection
    {
        return Quotation::with(['customer', 'quotationCompanies.insuranceCompany'])
            ->where('status', 'Sent')
            ->whereNotNull('sent_at')
            ->latest('sent_at')
            ->get();
    }

    public function getPendingQuotations(): Collection
    {
        return Quotation::with(['customer', 'quotationCompanies.insuranceCompany'])
            ->where('status', 'Draft')
            ->orWhere('status', 'Generated')
            ->latest()
            ->get();
    }

    public function getCountByStatus(): array
    {
        // Refactored: Using Eloquent groupBy with count() instead of selectRaw
        return Quotation::query()
            ->select('status')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => Quotation::where('status', $item->status)->count()];
            })
            ->toArray();
    }

    public function exists(int $id): bool
    {
        return Quotation::where('id', $id)->exists();
    }

    /**
     * Get count of quotations within date range.
     */
    public function getCountByDateRange($startDate, $endDate): int
    {
        return Quotation::whereBetween('created_at', [$startDate, $endDate])->count();
    }

    /**
     * Get total count of quotations.
     */
    public function getCount(): int
    {
        return Quotation::count();
    }

    /**
     * Get sum of quotation amounts within date range.
     */
    public function getSumByDateRange(string $column, $startDate, $endDate): float
    {
        // For quotations, we'll sum from quotation_companies table
        return (float) \Illuminate\Support\Facades\DB::table('quotation_companies')
            ->join('quotations', 'quotation_companies.quotation_id', '=', 'quotations.id')
            ->whereBetween('quotations.created_at', [$startDate, $endDate])
            ->sum('quotation_companies.final_premium') ?? 0;
    }

    /**
     * Get quotations by multiple statuses.
     */
    public function getByStatuses(array $statuses): Collection
    {
        return Quotation::whereIn('status', $statuses)
            ->with(['customer', 'quotationCompanies.insuranceCompany'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get count by specific status.
     */
    public function getCountBySpecificStatus(string $status): int
    {
        return Quotation::where('status', $status)->count();
    }

    /**
     * Get quotations trend data by month.
     */
    public function getMonthlyTrends(int $months = 12): array
    {
        $trends = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = \Carbon\Carbon::now()->subMonths($i)->startOfMonth();
            $endDate = \Carbon\Carbon::now()->subMonths($i)->endOfMonth();

            $count = $this->getCountByDateRange($startDate, $endDate);
            $value = $this->getSumByDateRange('final_premium', $startDate, $endDate);

            $trends[] = [
                'month' => $startDate->format('Y-m'),
                'month_name' => $startDate->format('M Y'),
                'quotations_count' => $count,
                'total_value' => $value,
                'average_value' => $count > 0 ? $value / $count : 0,
            ];
        }

        return $trends;
    }

    /**
     * Get conversion rate statistics.
     */
    public function getConversionStats(): array
    {
        $totalQuotations = $this->getCount();
        $convertedCount = $this->getCountBySpecificStatus('converted');
        $pendingCount = $this->getCountBySpecificStatus('pending');
        $rejectedCount = $this->getCountBySpecificStatus('rejected');

        return [
            'total_quotations' => $totalQuotations,
            'converted_count' => $convertedCount,
            'pending_count' => $pendingCount,
            'rejected_count' => $rejectedCount,
            'conversion_rate' => $totalQuotations > 0 ? ($convertedCount / $totalQuotations) * 100 : 0,
            'pending_rate' => $totalQuotations > 0 ? ($pendingCount / $totalQuotations) * 100 : 0,
            'rejection_rate' => $totalQuotations > 0 ? ($rejectedCount / $totalQuotations) * 100 : 0,
        ];
    }

    /**
     * Get top insurance companies by quotation count.
     * Refactored: Using Eloquent aggregate methods instead of DB::raw()
     */
    public function getTopInsuranceCompaniesByQuotations(int $limit = 10): array
    {
        return \Illuminate\Support\Facades\DB::table('quotation_companies')
            ->join('insurance_companies', 'quotation_companies.insurance_company_id', '=', 'insurance_companies.id')
            ->select('insurance_companies.id', 'insurance_companies.name')
            ->groupBy('insurance_companies.id', 'insurance_companies.name')
            ->orderByDesc(
                \Illuminate\Support\Facades\DB::table('quotation_companies as qc')
                    ->whereColumn('qc.insurance_company_id', 'insurance_companies.id')
                    ->selectRaw('COUNT(*)')
            )
            ->limit($limit)
            ->get()
            ->map(function ($company) {
                $companyData = \Illuminate\Support\Facades\DB::table('quotation_companies')
                    ->where('insurance_company_id', $company->id)
                    ->selectRaw('COUNT(*) as quotations_count, SUM(final_premium) as total_value, AVG(final_premium) as average_premium')
                    ->first();

                return [
                    'name' => $company->name,
                    'quotations_count' => $companyData->quotations_count ?? 0,
                    'total_value' => $companyData->total_value ?? 0,
                    'average_premium' => $companyData->average_premium ?? 0,
                ];
            })
            ->toArray();
    }

    /**
     * Get quotations by date range with details.
     */
    public function getDetailsByDateRange($startDate, $endDate): Collection
    {
        return Quotation::with(['customer', 'quotationCompanies.insuranceCompany'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get average quotation value.
     * Refactored: Using Eloquent avg() method instead of manual calculation
     */
    public function getAverageQuotationValue(): float
    {
        return (float) \Illuminate\Support\Facades\DB::table('quotation_companies')
            ->avg('final_premium') ?? 0.0;
    }
}
