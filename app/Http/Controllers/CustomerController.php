<?php

namespace App\Http\Controllers;

use App\Contracts\Services\CustomerServiceInterface;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Traits\ExportableTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

/**
 * Customer Controller
 *
 * Handles Customer CRUD operations.
 * Inherits middleware setup and common utilities from AbstractBaseCrudController.
 */
class CustomerController extends AbstractBaseCrudController
{
    use ExportableTrait;

    public function __construct(private CustomerServiceInterface $customerService)
    {
        $this->setupPermissionMiddleware('customer');
    }

    /**
     * List Customer
     *
     * @param Nill
     * @return array $customer
     *
     * @author Darshan Baraiya
     */
    public function index(Request $request)
    {
        try {
            // Handle AJAX requests for Select2 autocomplete
            if ($request->ajax() || $request->has('ajax')) {
                $search = $request->input('q', $request->input('search', ''));

                $query = Customer::query()->select('id', 'name');

                if ($search) {
                    $query->where('name', 'LIKE', sprintf('%%%s%%', $search))
                        ->orWhere('email', 'LIKE', sprintf('%%%s%%', $search))
                        ->orWhere('mobile', 'LIKE', sprintf('%%%s%%', $search));
                }

                $customers = $query->orderBy('name', 'asc')
                    ->limit(20)
                    ->get();

                // Format for Select2
                return response()->json([
                    'results' => $customers->map(fn ($customer): array => [
                        'id' => $customer->id,
                        'text' => $customer->name,
                    ]),
                ]);
            }

            $customers = $this->customerService->getCustomers($request);

            return view('customers.index', [
                'customers' => $customers,
                'sortField' => $request->input('sort_field', 'name'),
                'sortOrder' => $request->input('sort_order', 'asc'),
                'request' => $request->all(),
            ]);
        } catch (\Throwable $throwable) {
            // Create empty paginated result to maintain view compatibility
            $lengthAwarePaginator = new LengthAwarePaginator(
                collect(), // empty collection
                0, // total count
                10, // per page (matches CustomerService default)
                1, // current page
                ['path' => request()->url(), 'pageName' => 'page']
            );

            return view('customers.index', [
                'customers' => $lengthAwarePaginator,
                'sortField' => 'name',
                'sortOrder' => 'asc',
                'request' => $request->all(),
                'error' => 'Failed to load customers: '.$throwable->getMessage(),
            ]);
        }
    }

    /**
     * Create Customer
     *
     * @param Nill
     * @return array $customer
     *
     * @author Darshan Baraiya
     */
    public function create(): View
    {
        return view('customers.add');
    }

    /**
     * Store Customer
     *
     * @param  Request  $storeCustomerRequest
     * @return View Customers
     *
     * @author Darshan Baraiya
     */
    public function store(StoreCustomerRequest $storeCustomerRequest): RedirectResponse
    {
        try {
            $customer = $this->customerService->createCustomer($storeCustomerRequest);

            return $this->redirectWithSuccess('customers.index',
                $this->getSuccessMessage('Customer', 'created'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError(
                $this->getErrorMessage('Customer', 'create').': '.$throwable->getMessage())
                ->withInput();
        }
    }

    /**
     * Update Status Of Customer
     *
     * @return List Page With Success
     *
     * @author Darshan Baraiya
     */
    public function updateStatus(int $customer_id, int $status): RedirectResponse
    {
        try {
            $updated = $this->customerService->updateCustomerStatus($customer_id, $status);

            if ($updated) {
                return $this->redirectWithSuccess('customers.index',
                    $this->getSuccessMessage('Customer status', 'updated'));
            }

            return $this->redirectWithError('Failed to update customer status.');
        } catch (\Throwable $throwable) {
            return $this->redirectWithError(
                $this->getErrorMessage('Customer status', 'update').': '.$throwable->getMessage());
        }
    }

    /**
     * Show Customer Details - Redirects to Edit
     *
     * @param  Customer  $customer
     * @return RedirectResponse
     *
     * @author Claude Code
     */
    public function show(Customer $customer): RedirectResponse
    {
        return redirect()->route('customers.edit', $customer->id);
    }

    /**
     * Edit Customer
     *
     * @param  int  $customer
     * @return Collection $customer
     *
     * @author Darshan Baraiya
     */
    public function edit(Customer $customer): View
    {
        return view('customers.edit')->with([
            'customer' => $customer,
            'customer_insurances' => $customer->insurance,
        ]);
    }

    /**
     * Update Customer
     *
     * @param  Request  $updateCustomerRequest  ,  Customer $customer
     * @return View Customers
     *
     * @author Darshan Baraiya
     */
    public function update(UpdateCustomerRequest $updateCustomerRequest, Customer $customer): RedirectResponse
    {
        try {
            $updated = $this->customerService->updateCustomer($updateCustomerRequest, $customer);

            if ($updated) {
                return $this->redirectWithSuccess('customers.index',
                    $this->getSuccessMessage('Customer', 'updated'));
            }

            return $this->redirectWithError('Failed to update customer.');
        } catch (\Throwable $throwable) {
            return $this->redirectWithError(
                $this->getErrorMessage('Customer', 'update').': '.$throwable->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete Customer
     *
     * @return Index Customers
     *
     * @author Darshan Baraiya
     */
    public function delete(Customer $customer): RedirectResponse
    {
        try {
            $deleted = $this->customerService->deleteCustomer($customer);

            if ($deleted) {
                return $this->redirectWithSuccess('customers.index',
                    $this->getSuccessMessage('Customer', 'deleted'));
            }

            return $this->redirectWithError('Failed to delete customer.');
        } catch (\Throwable $throwable) {
            return $this->redirectWithError(
                $this->getErrorMessage('Customer', 'delete').': '.$throwable->getMessage());
        }
    }

    /**
     * Import Customers
     *
     * @param null
     * @return View File
     */
    public function importCustomers(): View
    {
        return view('customers.import');
    }

    protected function getExportRelations(): array
    {
        return ['familyGroup'];
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'email', 'mobile_number'];
    }

    protected function getExportConfig(Request $request): array
    {
        return [
            'format' => $request->get('format', 'xlsx'),
            'filename' => 'customers',
            'with_headings' => true,
            'auto_size' => true,
            'relations' => $this->getExportRelations(),
            'order_by' => $this->getExportOrderBy(),
            'headings' => ['ID', 'Name', 'Email', 'Mobile', 'Status', 'Family Group', 'Created Date'],
            'mapping' => fn ($customer): array => [
                $customer->id,
                $customer->name,
                $customer->email,
                $customer->mobile_number,
                ucfirst((string) $customer->status),
                $customer->familyGroup ? $customer->familyGroup->name : 'Individual',
                $customer->created_at->format('Y-m-d H:i:s'),
            ],
            'with_mapping' => true,
        ];
    }

    public function resendOnBoardingWA(Customer $customer): RedirectResponse
    {
        $sent = $this->customerService->sendOnboardingMessage($customer);

        if ($sent) {
            return $this->redirectWithSuccess('customers.index',
                $this->getSuccessMessage('Onboarding message', 'sent'));
        }

        return $this->redirectWithError('Failed to send onboarding message.');
    }
}
