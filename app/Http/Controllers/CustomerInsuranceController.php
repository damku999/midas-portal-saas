<?php

namespace App\Http\Controllers;

use App\Contracts\Services\CustomerInsuranceServiceInterface;
use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Traits\WhatsAppApiTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Customer Insurance Controller
 *
 * Handles CustomerInsurance CRUD operations.
 * Inherits middleware setup and common utilities from AbstractBaseCrudController.
 */
class CustomerInsuranceController extends AbstractBaseCrudController
{
    use WhatsAppApiTrait;

    public function __construct(
        private CustomerInsuranceServiceInterface $customerInsuranceService
    ) {
        $this->setupCustomPermissionMiddleware([
            ['permission' => 'customer-insurance-list|customer-insurance-create|customer-insurance-edit|customer-insurance-delete', 'only' => ['index']],
            ['permission' => 'customer-insurance-create', 'only' => ['create', 'store', 'updateStatus']],
            ['permission' => 'customer-insurance-edit', 'only' => ['edit', 'update', 'renew', 'storeRenew']],
            ['permission' => 'customer-insurance-delete', 'only' => ['delete']],
        ]);
    }

    /**
     * List CustomerInsurance
     *
     * @return View
     *
     * @author Darshan Baraiya
     */
    public function index(Request $request)
    {
        // Handle AJAX requests for Select2 autocomplete
        if ($request->ajax() || $request->has('ajax')) {
            $search = $request->input('q', $request->input('search', ''));

            $query = CustomerInsurance::with('customer:id,name')
                ->select('id', 'policy_no', 'customer_id', 'registration_no');

            if ($search) {
                $query->where(static function ($q) use ($search): void {
                    $q->where('policy_no', 'LIKE', sprintf('%%%s%%', $search))
                        ->orWhere('registration_no', 'LIKE', sprintf('%%%s%%', $search))
                        ->orWhereHas('customer', static function ($cq) use ($search): void {
                            $cq->where('name', 'LIKE', sprintf('%%%s%%', $search));
                        });
                });
            }

            $insurances = $query->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            // Format for Select2
            return response()->json([
                'results' => $insurances->map(fn ($insurance): array => [
                    'id' => $insurance->id,
                    'text' => $insurance->policy_no.' - '.($insurance->customer?->name ?? 'Unknown'),
                ]),
            ]);
        }

        $lengthAwarePaginator = $this->customerInsuranceService->getCustomerInsurances($request);
        $customers = Customer::query()->select('id', 'name')->get();

        return view('customer_insurances.index', [
            'customer_insurances' => $lengthAwarePaginator,
            'customers' => $customers,
            'sort' => $request->input('sort', 'id'),
            'direction' => $request->input('direction', 'desc'),
            'request' => $request->all(),
        ]);
    }

    /**
     * Create CustomerInsurance
     *
     * @return View
     *
     * @author Darshan Baraiya
     */
    public function create()
    {
        $formData = $this->customerInsuranceService->getFormData();

        return view('customer_insurances.add', $formData);
    }

    /**
     * Store CustomerInsurance
     *
     *
     * @author Darshan Baraiya
     */
    public function store(Request $request): RedirectResponse
    {
        $validationRules = $this->customerInsuranceService->getStoreValidationRules();
        $request->validate($validationRules);

        try {
            $data = $this->customerInsuranceService->prepareStorageData($request);
            $customer_insurance = $this->customerInsuranceService->createCustomerInsurance($data);

            // Handle file uploads
            $this->customerInsuranceService->handleFileUpload($request, $customer_insurance);

            // Send WhatsApp document if uploaded
            if (! empty($customer_insurance->policy_document_path)) {
                $this->customerInsuranceService->sendWhatsAppDocument($customer_insurance);
            }

            return $this->redirectWithSuccess('customer_insurances.index', $this->getSuccessMessage('Customer Insurance', 'created'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Customer Insurance', 'create').': '.$throwable->getMessage());
        }
    }

    /**
     * Update Status Of CustomerInsurance
     *
     *
     * @author Darshan Baraiya
     */
    public function updateStatus(int $customer_insurance_id, int $status): RedirectResponse
    {
        // Validation
        $validator = Validator::make([
            'customer_insurance_id' => $customer_insurance_id,
            'status' => $status,
        ], [
            'customer_insurance_id' => 'required|exists:customer_insurances,id',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->redirectWithError($validator->errors()->first());
        }

        try {
            $this->customerInsuranceService->updateStatus($customer_insurance_id, $status);

            return $this->redirectWithSuccess('customer_insurances.index', $this->getSuccessMessage('Customer Insurance Status', 'updated'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Customer Insurance', 'operation').': '.$throwable->getMessage());
        }
    }

    /**
     * Edit CustomerInsurance
     *
     * @return View
     *
     * @author Darshan Baraiya
     */
    public function edit(CustomerInsurance $customerInsurance)
    {
        $formData = $this->customerInsuranceService->getFormData();
        $formData['customer_insurance'] = $customerInsurance;

        return view('customer_insurances.edit', $formData);
    }

    /**
     * Send WhatsApp Document
     *
     *
     * @author Darshan Baraiya
     */
    public function sendWADocument(CustomerInsurance $customerInsurance): RedirectResponse
    {
        try {
            $sent = $this->customerInsuranceService->sendWhatsAppDocument($customerInsurance);

            if ($sent) {
                return $this->redirectWithSuccess('customer_insurances.index', 'Document Sent Successfully!');
            }

            return $this->redirectWithError('Document Not Sent!');
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Customer Insurance', 'operation').': '.$throwable->getMessage());
        }
    }

    /**
     * Send Renewal Reminder via WhatsApp
     */
    public function sendRenewalReminderWA(CustomerInsurance $customerInsurance): RedirectResponse
    {
        try {
            $this->customerInsuranceService->sendRenewalReminderWhatsApp($customerInsurance);

            return $this->redirectWithSuccess('customer_insurances.index', 'Renewal Reminder Sent Successfully!');
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Customer Insurance', 'operation').': '.$throwable->getMessage());
        }
    }

    /**
     * Update CustomerInsurance
     *
     *
     * @author Darshan Baraiya
     */
    public function update(Request $request, CustomerInsurance $customerInsurance): RedirectResponse
    {
        $validationRules = $this->customerInsuranceService->getUpdateValidationRules();
        $request->validate($validationRules);

        try {
            $data = $this->customerInsuranceService->prepareStorageData($request);
            $this->customerInsuranceService->updateCustomerInsurance($customerInsurance, $data);

            // Handle file uploads
            $this->customerInsuranceService->handleFileUpload($request, $customerInsurance);

            return $this->redirectWithSuccess('customer_insurances.index', $this->getSuccessMessage('Customer Insurance', 'updated'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Customer Insurance', 'create').': '.$throwable->getMessage());
        }
    }

    /**
     * Delete CustomerInsurance
     *
     *
     * @author Darshan Baraiya
     */
    public function delete(CustomerInsurance $customerInsurance): RedirectResponse
    {
        try {
            $this->customerInsuranceService->deleteCustomerInsurance($customerInsurance);

            return $this->redirectWithSuccess('customer_insurances.index', $this->getSuccessMessage('Customer Insurance', 'deleted'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Customer Insurance', 'operation').': '.$throwable->getMessage());
        }
    }

    /**
     * Import CustomerInsurances
     *
     * @return View
     */
    public function importCustomerInsurances()
    {
        return view('customer_insurances.import');
    }

    /**
     * Export CustomerInsurances
     */
    public function export(): BinaryFileResponse
    {
        return $this->customerInsuranceService->exportCustomerInsurances();
    }

    /**
     * Renew CustomerInsurance
     *
     * @return View
     *
     * @author Darshan Baraiya
     */
    public function renew(CustomerInsurance $customerInsurance)
    {
        $formData = $this->customerInsuranceService->getFormData();
        $formData['customer_insurance'] = $customerInsurance;

        return view('customer_insurances.renew', $formData);
    }

    /**
     * Store Renew CustomerInsurance
     *
     *
     * @author Darshan Baraiya
     */
    public function storeRenew(Request $request, CustomerInsurance $customerInsurance): RedirectResponse
    {
        $validationRules = $this->customerInsuranceService->getRenewalValidationRules();
        $request->validate($validationRules);

        try {
            $data = $this->customerInsuranceService->prepareStorageData($request);
            $renewedPolicy = $this->customerInsuranceService->renewPolicy($customerInsurance, $data);

            // Handle file uploads
            $this->customerInsuranceService->handleFileUpload($request, $renewedPolicy);

            // Send WhatsApp document if uploaded
            if (! empty($renewedPolicy->policy_document_path)) {
                $this->customerInsuranceService->sendWhatsAppDocument($renewedPolicy);
            }

            return $this->redirectWithSuccess('customer_insurances.index', $this->getSuccessMessage('Customer Insurance', 'renewed'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Customer Insurance', 'create').': '.$throwable->getMessage());
        }
    }
}
