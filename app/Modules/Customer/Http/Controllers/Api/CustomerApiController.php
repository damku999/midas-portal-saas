<?php

namespace App\Modules\Customer\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Modules\Customer\Contracts\CustomerServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerApiController extends Controller
{
    public function __construct(
        private CustomerServiceInterface $customerService
    ) {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of customers with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $customers = $this->customerService->getCustomers($request);

            return response()->json([
                'success' => true,
                'data' => $customers->items(),
                'pagination' => [
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                    'from' => $customers->firstItem(),
                    'to' => $customers->lastItem(),
                ],
                'filters' => [
                    'search' => $request->input('search'),
                    'type' => $request->input('type'),
                    'status' => $request->input('status'),
                    'from_date' => $request->input('from_date'),
                    'to_date' => $request->input('to_date'),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customers',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created customer.
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        try {
            $customer = $this->customerService->createCustomer($request);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => $customer->load(['familyGroup']),
            ], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): JsonResponse
    {
        try {
            $customer->load(['familyGroup', 'customerAuditLogs.user']);

            return response()->json([
                'success' => true,
                'data' => $customer,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customer',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified customer.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        try {
            $updated = $this->customerService->updateCustomer($request, $customer);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer updated successfully',
                    'data' => $customer->fresh()->load(['familyGroup']),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        try {
            $deleted = $this->customerService->deleteCustomer($customer);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer deleted successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete customer',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete customer',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update customer status.
     */
    public function updateStatus(Request $request, Customer $customer): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:0,1',
        ]);

        try {
            $updated = $this->customerService->updateCustomerStatus(
                $customer->id,
                $request->input('status')
            );

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer status updated successfully',
                    'data' => [
                        'id' => $customer->id,
                        'status' => $request->input('status'),
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer status',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer status',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search customers by query.
     */
    public function search(string $query): JsonResponse
    {
        try {
            $customers = $this->customerService->searchCustomers($query);

            return response()->json([
                'success' => true,
                'data' => $customers,
                'query' => $query,
                'count' => $customers->count(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search customers',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get customers by type.
     */
    public function getByType(string $type): JsonResponse
    {
        try {
            if (! in_array($type, ['Retail', 'Corporate'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid customer type. Must be Retail or Corporate.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $customers = $this->customerService->getCustomersByType($type);

            return response()->json([
                'success' => true,
                'data' => $customers,
                'type' => $type,
                'count' => $customers->count(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customers by type',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get customers by family group.
     */
    public function getByFamily(int $familyGroupId): JsonResponse
    {
        try {
            $customers = $this->customerService->getCustomersByFamily($familyGroupId);

            return response()->json([
                'success' => true,
                'data' => $customers,
                'family_group_id' => $familyGroupId,
                'count' => $customers->count(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customers by family group',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get customer statistics.
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $statistics = $this->customerService->getCustomerStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'generated_at' => now()->toISOString(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customer statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send onboarding message to customer.
     */
    public function sendOnboardingMessage(Customer $customer): JsonResponse
    {
        try {
            $sent = $this->customerService->sendOnboardingMessage($customer);

            return response()->json([
                'success' => $sent,
                'message' => $sent ? 'Onboarding message sent successfully' : 'Failed to send onboarding message',
                'data' => [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'mobile_number' => $customer->mobile_number,
                ],
            ], $sent ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send onboarding message',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get active customers for selection dropdowns.
     */
    public function getActiveForSelection(): JsonResponse
    {
        try {
            $customers = $this->customerService->getActiveCustomersForSelection();

            return response()->json([
                'success' => true,
                'data' => $customers->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'mobile_number' => $customer->mobile_number,
                        'type' => $customer->type,
                    ];
                }),
                'count' => $customers->count(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve active customers',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
