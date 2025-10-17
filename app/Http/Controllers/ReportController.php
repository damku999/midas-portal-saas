<?php

namespace App\Http\Controllers;

use App\Contracts\Services\ReportServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Report Controller
 *
 * Handles Report operations.
 * Inherits middleware setup and common utilities from AbstractBaseCrudController.
 */
class ReportController extends AbstractBaseCrudController
{
    public function __construct(
        private readonly ReportServiceInterface $reportService
    ) {
        $this->setupCustomPermissionMiddleware([
            ['permission' => 'report-list', 'only' => ['index']],
        ]);
    }

    public function index(Request $request)
    {
        $response = $this->reportService->getInitialData();

        if (($request->has('view') || $request->isMethod('post')) && ! empty($request->input('report_name'))) {
            // Validate based on report type
            $rules = ['report_name' => 'required|in:cross_selling,insurance_detail,due_policy_detail'];

            // Due policy report REQUIRES date range
            if ($request['report_name'] === 'due_policy_detail') {
                $rules['due_start_date'] = 'required';
                $rules['due_end_date'] = 'required';
            }

            $request->validate($rules, [
                'due_start_date.required' => 'Due Policy Period (From Month) is required for this report.',
                'due_end_date.required' => 'Due Policy Period (To Month) is required for this report.',
            ]);

            Log::info('Processing report request', [
                'report_name' => $request['report_name'],
                'request_data' => $request->all(),
            ]);

            if ($request['report_name'] == 'cross_selling') {
                $crossSellingData = $this->reportService->generateCrossSellingReport($request->all());
                $response['cross_selling_report'] = $crossSellingData['crossSelling'] ?? [];
                $response['premiumTypes'] = $crossSellingData['premiumTypes'] ?? [];
            } elseif ($request['report_name'] == 'insurance_detail') {
                $insuranceData = $this->reportService->generateCustomerInsuranceReport($request->all());
                Log::info('Insurance data received', ['count' => count($insuranceData)]);
                $response['insurance_reports'] = $insuranceData;
            } elseif ($request['report_name'] == 'due_policy_detail') {
                Log::info('Due Policy Report - Request Data', [
                    'due_start_date' => $request->input('due_start_date'),
                    'due_end_date' => $request->input('due_end_date'),
                    'all_filters' => $request->all(),
                ]);

                $duePolicyData = $this->reportService->generateCustomerInsuranceReport($request->all());

                Log::info('Due policy data received', [
                    'count' => count($duePolicyData),
                    'first_record' => $duePolicyData[0] ?? 'no records',
                ]);

                $response['due_policy_reports'] = $duePolicyData;
            }
        }

        Log::info('Final response data', ['response_keys' => array_keys($response)]);

        return view('reports.index', $response);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $request->validate(['report_name' => 'required']);
        if ($request['report_name'] == 'cross_selling') {
            return $this->reportService->exportCrossSellingReport($request->all());
        }
        if ($request['report_name'] == 'insurance_detail') {
            return $this->reportService->exportCustomerInsuranceReport($request->all());
        }

        if ($request['report_name'] == 'due_policy_detail') {
            return $this->reportService->exportCustomerInsuranceReport($request->all());
        } else {
            throw new \Exception('Invalid report type: '.$request['report_name']);
        }
    }

    public function saveColumns(Request $request): void
    {
        $this->reportService->saveUserReportColumns(
            $request->input('report_name'),
            $request->selected_columns,
            auth()->user()->id
        );
    }

    public function loadColumns(Request $request, string $report_name)
    {
        $columns = $this->reportService->loadUserReportColumns($report_name, auth()->user()->id);
        $report = (object) ['selected_columns' => $columns];

        return view('reports.table_columns', ['reports' => $report]);
    }
}
