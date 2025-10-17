<?php

namespace App\Http\Controllers;

use App\Contracts\Services\InsuranceCompanyServiceInterface;
use App\Models\InsuranceCompany;
use App\Traits\ExportableTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Insurance Company Controller
 *
 * Handles InsuranceCompany CRUD operations.
 * Inherits middleware setup and common utilities from AbstractBaseCrudController.
 */
class InsuranceCompanyController extends AbstractBaseCrudController
{
    use ExportableTrait;

    public function __construct(
        private InsuranceCompanyServiceInterface $insuranceCompanyService
    ) {
        $this->setupPermissionMiddleware('insurance_company');
    }

    /**
     * List InsuranceCompany
     *
     * @param void
     * @return array
     *
     * @author Darshan Baraiya
     */
    public function index(Request $request)
    {
        $lengthAwarePaginator = $this->insuranceCompanyService->getInsuranceCompanies($request);

        return view('insurance_companies.index', ['insurance_companies' => $lengthAwarePaginator, 'request' => $request->all()]);
    }

    /**
     * Create InsuranceCompany
     *
     * @param void
     * @return array
     *
     * @author Darshan Baraiya
     */
    public function create()
    {
        return view('insurance_companies.add');
    }

    /**
     * Store InsuranceCompany
     *
     * @return View InsuranceCompanys
     *
     * @author Darshan Baraiya
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        try {
            $this->insuranceCompanyService->createInsuranceCompany([
                'name' => $request->name,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
            ]);

            return $this->redirectWithSuccess('insurance_companies.index',
                $this->getSuccessMessage('Insurance Company', 'created'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError(
                $this->getErrorMessage('Insurance Company', 'create').': '.$throwable->getMessage())
                ->withInput();
        }
    }

    /**
     * Update Status Of InsuranceCompany
     *
     * @return RedirectResponse Page With Success
     *
     * @author Darshan Baraiya
     */
    public function updateStatus(int $insurance_company_id, int $status): RedirectResponse
    {
        try {
            $this->insuranceCompanyService->updateStatus($insurance_company_id, $status);

            return $this->redirectWithSuccess('insurance_companies.index',
                $this->getSuccessMessage('Insurance Company status', 'updated'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError(
                $this->getErrorMessage('Insurance Company status', 'update').': '.$throwable->getMessage());
        }
    }

    /**
     * Edit InsuranceCompany
     *
     * @param  int  $insuranceCompany
     * @return Collection $insurance_company
     *
     * @author Darshan Baraiya
     */
    public function edit(InsuranceCompany $insuranceCompany)
    {
        return view('insurance_companies.edit')->with([
            'insurance_company' => $insuranceCompany,
        ]);
    }

    /**
     * Update InsuranceCompany
     *
     * @return View InsuranceCompanys
     *
     * @author Darshan Baraiya
     */
    public function update(Request $request, InsuranceCompany $insuranceCompany)
    {
        $request->validate([
            'name' => 'required',
        ]);

        try {
            $this->insuranceCompanyService->updateInsuranceCompany($insuranceCompany, [
                'name' => $request->name,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
            ]);

            return $this->redirectWithSuccess('insurance_companies.index',
                $this->getSuccessMessage('Insurance Company', 'updated'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError(
                $this->getErrorMessage('Insurance Company', 'update').': '.$throwable->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete InsuranceCompany
     *
     * @return RedirectResponse InsuranceCompanys
     *
     * @author Darshan Baraiya
     */
    public function delete(InsuranceCompany $insuranceCompany): RedirectResponse
    {
        try {
            $this->insuranceCompanyService->deleteInsuranceCompany($insuranceCompany);

            return $this->redirectWithSuccess('insurance_companies.index',
                $this->getSuccessMessage('Insurance Company', 'deleted'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError(
                $this->getErrorMessage('Insurance Company', 'delete').': '.$throwable->getMessage());
        }
    }

    protected function getExportRelations(): array
    {
        return [];
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'email', 'mobile_number'];
    }

    protected function getExportConfig(Request $request): array
    {
        return [
            'format' => $request->get('format', 'xlsx'),
            'filename' => 'insurance_companies',
            'with_headings' => true,
            'auto_size' => true,
            'relations' => $this->getExportRelations(),
            'order_by' => ['column' => 'created_at', 'direction' => 'desc'],
            'headings' => ['ID', 'Name', 'Email', 'Mobile Number', 'Status', 'Created Date'],
            'mapping' => fn ($model): array => [
                $model->id,
                $model->name,
                $model->email ?? 'N/A',
                $model->mobile_number ?? 'N/A',
                $model->status ? 'Active' : 'Inactive',
                $model->created_at->format('Y-m-d H:i:s'),
            ],
            'with_mapping' => true,
        ];
    }
}
