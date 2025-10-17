<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\User;
use App\Rules\MatchOldPassword;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HomeController extends AbstractBaseCrudController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index(Request $request)
    {
        if (! Auth::user()->hasRole('Admin')) {
            return redirect()->route('customers.index');
        }

        $dashboardData = $this->prepareDashboardData($request);

        return view('home', $dashboardData);
    }

    /**
     * Prepare all dashboard data
     */
    private function prepareDashboardData(Request $request): array
    {
        $compressionData = $this->getCompressionData($request);
        $customerStats = $this->getCustomerStatistics();
        $financialStats = $this->getFinancialStatistics();
        $renewalStats = $this->getRenewalStatistics();
        $chartData = $this->getFinancialYearChartData();

        return array_merge(
            $compressionData,
            $customerStats,
            $financialStats,
            $renewalStats,
            $chartData
        );
    }

    /**
     * Get customer statistics
     */
    private function getCustomerStatistics(): array
    {
        $totalCustomers = Customer::query()->count();
        $activeCustomers = Customer::query()->where('status', 1)->count();

        $totalInsurances = CustomerInsurance::query()->count();
        $activeInsurances = CustomerInsurance::query()->where('status', 1)->count();

        return [
            'total_customer' => $totalCustomers,
            'active_customer' => $activeCustomers,
            'inactive_customer' => $totalCustomers - $activeCustomers,
            'total_customer_insurance' => $totalInsurances,
            'active_customer_insurance' => $activeInsurances,
            'inactive_customer_insurance' => $totalInsurances - $activeInsurances,
        ];
    }

    /**
     * Get financial statistics for lifetime, current month, and last month
     */
    private function getFinancialStatistics(): array
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            // Lifetime totals
            'life_time_final_premium_with_gst' => CustomerInsurance::query()->sum('final_premium_with_gst'),
            'life_time_my_commission_amount' => CustomerInsurance::query()->sum('my_commission_amount'),
            'life_time_transfer_commission_amount' => CustomerInsurance::query()->sum('transfer_commission_amount'),
            'life_time_actual_earnings' => CustomerInsurance::query()->sum('actual_earnings'),

            // Current month totals
            ...$this->getMonthlyFinancialData($currentMonth, 'current_month'),

            // Last month totals
            ...$this->getMonthlyFinancialData($lastMonth, 'last_month'),

            // Expiring insurances this month
            'expiring_customer_insurance' => $this->getExpiringInsurancesCount(),
        ];
    }

    /**
     * Get financial data for a specific month
     */
    private function getMonthlyFinancialData(Carbon $month, string $prefix): array
    {
        $startDate = $month->format('Y-m-d');
        $endDate = $month->copy()->endOfMonth()->format('Y-m-d');

        $data = CustomerInsurance::query()->whereBetween('issue_date', [$startDate, $endDate])
            ->selectRaw('
                COALESCE(SUM(final_premium_with_gst), 0) as final_premium_with_gst,
                COALESCE(SUM(my_commission_amount), 0) as my_commission_amount,
                COALESCE(SUM(transfer_commission_amount), 0) as transfer_commission_amount,
                COALESCE(SUM(actual_earnings), 0) as actual_earnings
            ')
            ->first();

        return [
            $prefix.'_final_premium_with_gst' => $data->final_premium_with_gst,
            $prefix.'_my_commission_amount' => $data->my_commission_amount,
            $prefix.'_transfer_commission_amount' => $data->transfer_commission_amount,
            $prefix.'_actual_earnings' => $data->actual_earnings,
        ];
    }

    /**
     * Get renewal statistics for current month
     */
    private function getRenewalStatistics(): array
    {
        $currentMonth = Carbon::now();

        $baseConditions = [
            ['expired_date', '>=', $currentMonth->startOfMonth()],
            ['expired_date', '<=', $currentMonth->copy()->endOfMonth()],
        ];

        return [
            'total_renewing_this_month' => CustomerInsurance::query()->where($baseConditions)->count(),
            'already_renewed_this_month' => CustomerInsurance::query()->where($baseConditions)->where('is_renewed', 1)->count(),
            'pending_renewal_this_month' => CustomerInsurance::query()->where($baseConditions)->where('is_renewed', 0)->count(),
        ];
    }

    /**
     * Get count of expiring customer insurances for current month
     */
    private function getExpiringInsurancesCount(): int
    {
        return CustomerInsurance::query()->where('status', 0)
            ->whereMonth('expired_date', Carbon::now()->month)
            ->whereYear('expired_date', Carbon::now()->year)
            ->count();
    }

    /**
     * Get financial year chart data
     */
    private function getFinancialYearChartData(): array
    {
        $financialYearDates = $this->getFinancialYearDates();

        $financialYearData = CustomerInsurance::query()->whereBetween('issue_date', [
            $financialYearDates['start']->format('Y-m-d'),
            $financialYearDates['end']->format('Y-m-d'),
        ])->get();

        $groupedData = $this->groupDataByMonth($financialYearData);

        return ['json_data' => json_encode($groupedData)];
    }

    /**
     * Get financial year start and end dates
     */
    private function getFinancialYearDates(?Carbon $date = null): array
    {
        $date ??= Carbon::now();
        $currentMonth = $date->month;
        $currentYear = $date->year;

        if ($currentMonth >= 4) {
            // Financial year: April current year to March next year
            $start = Carbon::create($currentYear, 4, 1, 0, 0, 0);
            $end = Carbon::create($currentYear + 1, 3, 31, 23, 59, 59);
        } else {
            // Financial year: April previous year to March current year
            $start = Carbon::create($currentYear - 1, 4, 1, 0, 0, 0);
            $end = Carbon::create($currentYear, 3, 31, 23, 59, 59);
        }

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Group financial data by month
     */
    private function groupDataByMonth($data): array
    {
        $groupedData = $data->groupBy(fn ($item) => Carbon::parse($item->issue_date)->format('Y-m'));

        $result = [];
        foreach ($groupedData as $month => $monthData) {
            $monthKey = Carbon::createFromFormat('Y-m', $month)->format('m-Y');

            $result[$monthKey] = [
                'final_premium_with_gst' => $monthData->sum('final_premium_with_gst'),
                'my_commission_amount' => $monthData->sum('my_commission_amount'),
                'transfer_commission_amount' => $monthData->sum('transfer_commission_amount'),
                'actual_earnings' => $monthData->sum('actual_earnings'),
            ];
        }

        ksort($result);

        return $result;
    }

    /**
     * Get compression data for dashboard analytics
     */
    public function getCompressionData(Request $request): array
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::now();
        $currentYear = $date->year;

        $financialYearDates = $this->getFinancialYearDates($date);
        $previousFinancialYearDates = $this->getFinancialYearDates($date->copy()->subYear());

        $sumColumns = $this->getSumColumns();

        return ['data' => [
            'date' => $date,
            'yesterday' => $date->copy()->subDay()->format('Y-m-d'),
            'day_before_yesterday' => $date->copy()->subDays(2)->format('Y-m-d'),
            'financial_year_start' => $financialYearDates['start'],
            'financial_year_end' => $financialYearDates['end'],
            'previous_financial_year_start' => $previousFinancialYearDates['start'],
            'previous_financial_year_end' => $previousFinancialYearDates['end'],
            'current_year_data' => $this->getFinancialDataForPeriod(
                $financialYearDates['start'],
                $financialYearDates['end'],
                $sumColumns
            ),
            'last_year_data' => $this->getFinancialDataForPeriod(
                $previousFinancialYearDates['start'],
                $previousFinancialYearDates['end'],
                $sumColumns
            ),
            'today_data' => $this->getFinancialDataForDate($date, $sumColumns),
            'yesterday_data' => $this->getFinancialDataForDate($date->copy()->subDay(), $sumColumns),
            'day_before_yesterday_data' => $this->getFinancialDataForDate($date->copy()->subDays(2), $sumColumns),
            'quarters_data' => $this->getQuarterlyData($financialYearDates['start'], $sumColumns),
            'quarter_date' => $this->getQuarterDates($financialYearDates['start']), ],
        ];
    }

    /**
     * Get sum columns for financial calculations
     */
    private function getSumColumns(): array
    {
        return [
            DB::raw('COALESCE(SUM(final_premium_with_gst), 0) AS sum_final_premium'),
            DB::raw('COALESCE(SUM(my_commission_amount), 0) AS sum_my_commission'),
            DB::raw('COALESCE(SUM(transfer_commission_amount), 0) AS sum_transfer_commission'),
            DB::raw('COALESCE(SUM(actual_earnings), 0) AS sum_actual_earnings'),
        ];
    }

    /**
     * Get financial data for a specific period
     */
    private function getFinancialDataForPeriod(Carbon $startDate, Carbon $endDate, array $columns): array
    {
        return CustomerInsurance::query()->select($columns)
            ->whereBetween('issue_date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
            ])
            ->first()
            ->toArray();
    }

    /**
     * Get financial data for a specific date
     */
    private function getFinancialDataForDate(Carbon $date, array $columns): array
    {
        return CustomerInsurance::query()->select($columns)
            ->where('issue_date', $date->format('Y-m-d'))
            ->first()
            ->toArray();
    }

    /**
     * Get quarterly financial data
     */
    private function getQuarterlyData(Carbon $financialYearStart, array $columns): array
    {
        $quarters = [];

        for ($i = 0; $i < 4; $i++) {
            $quarterStart = $financialYearStart->copy()->addMonths($i * 3);
            $quarterEnd = $quarterStart->copy()->addMonths(2)->endOfMonth();

            $quarters[] = $this->getFinancialDataForPeriod($quarterStart, $quarterEnd, $columns);
        }

        return $quarters;
    }

    /**
     * Get quarter date ranges
     */
    private function getQuarterDates(Carbon $financialYearStart): array
    {
        $quarterDates = [];

        for ($i = 0; $i < 4; $i++) {
            $quarterStart = $financialYearStart->copy()->addMonths($i * 3);
            $quarterEnd = $quarterStart->copy()->addMonths(2)->endOfMonth();

            $quarterDates[] = [
                'quarter_start' => $quarterStart,
                'quarter_end' => $quarterEnd,
            ];
        }

        return $quarterDates;
    }

    /**
     * Show user profile page
     */
    public function getProfile()
    {
        return view('profile');
    }

    /**
     * Update user profile information
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile_number' => 'required|numeric|digits:10',
        ]);

        try {
            DB::beginTransaction();

            $this->updateUserProfile($request);

            DB::commit();

            return $this->redirectWithSuccess(null, 'Profile updated successfully.');
        } catch (\Throwable $throwable) {
            DB::rollBack();

            return $this->redirectWithError('Failed to update profile: '.$throwable->getMessage());
        }
    }

    /**
     * Update user profile data
     */
    private function updateUserProfile(Request $request): void
    {
        User::whereId(auth()->id())->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'mobile_number' => $request->mobile_number,
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required', 'min:8', 'confirmed'],
            'new_password_confirmation' => ['required'],
        ]);

        try {
            DB::beginTransaction();

            $this->updateUserPassword($request->new_password);

            DB::commit();

            return $this->redirectWithSuccess(null, 'Password changed successfully.');
        } catch (\Throwable $throwable) {
            DB::rollBack();

            return $this->redirectWithError('Failed to change password: '.$throwable->getMessage());
        }
    }

    /**
     * Update user password
     */
    private function updateUserPassword(string $newPassword): void
    {
        User::query()->find(auth()->id())->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}
