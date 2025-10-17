<?php

namespace App\Modules\Customer\Services;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Events\Customer\CustomerProfileUpdated;
use App\Events\Customer\CustomerRegistered;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Modules\Customer\Contracts\CustomerServiceInterface;
use App\Services\FileUploadService;
use App\Traits\WhatsAppApiTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerService implements CustomerServiceInterface
{
    use WhatsAppApiTrait;

    public function __construct(
        private CustomerRepositoryInterface $customerRepository,
        private FileUploadService $fileUploadService
    ) {}

    public function getCustomers(Request $request): LengthAwarePaginator
    {
        $filters = [
            'search' => $request->input('search'),
            'type' => $request->input('type'),
            'status' => $request->input('status'),
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'sort_field' => $request->input('sort_field', 'name'),
            'sort_order' => $request->input('sort_order', 'asc'),
        ];

        return $this->customerRepository->getPaginated($filters, 10);
    }

    public function createCustomer(StoreCustomerRequest $request): Customer
    {
        DB::beginTransaction();

        try {
            $customer = $this->customerRepository->create([
                'name' => $request->name,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
                'status' => $request->status,
                'wedding_anniversary_date' => $request->wedding_anniversary_date,
                'engagement_anniversary_date' => $request->engagement_anniversary_date,
                'date_of_birth' => $request->date_of_birth,
                'type' => $request->type,
                'pan_card_number' => $request->pan_card_number,
                'aadhar_card_number' => $request->aadhar_card_number,
                'gst_number' => $request->gst_number,
            ]);

            $this->handleCustomerDocuments($request, $customer);

            DB::commit();

            // Fire CustomerRegistered event for async processing
            CustomerRegistered::dispatch(
                $customer,
                [
                    'request_data' => $request->only(['type', 'status']),
                    'has_documents' => $request->hasFile('documents'),
                ],
                auth()->user()->name ?? 'system'
            );

            return $customer;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function updateCustomer(UpdateCustomerRequest $request, Customer $customer): bool
    {
        DB::beginTransaction();

        try {
            $originalValues = $customer->only([
                'name', 'email', 'mobile_number', 'status', 'type',
                'pan_card_number', 'aadhar_card_number', 'gst_number',
            ]);

            $newValues = [
                'name' => $request->name,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
                'status' => $request->status,
                'wedding_anniversary_date' => $request->wedding_anniversary_date,
                'engagement_anniversary_date' => $request->engagement_anniversary_date,
                'date_of_birth' => $request->date_of_birth,
                'type' => $request->type,
                'pan_card_number' => $request->pan_card_number,
                'aadhar_card_number' => $request->aadhar_card_number,
                'gst_number' => $request->gst_number,
            ];

            $updated = $this->customerRepository->update($customer->id, $newValues);

            if ($updated) {
                $this->handleCustomerDocuments($request, $customer);
                DB::commit();

                // Identify changed fields
                $changedFields = [];
                foreach ($newValues as $field => $newValue) {
                    if (isset($originalValues[$field]) && $originalValues[$field] !== $newValue) {
                        $changedFields[] = $field;
                    }
                }

                // Fire CustomerProfileUpdated event if there are changes
                if (! empty($changedFields)) {
                    CustomerProfileUpdated::dispatch(
                        $customer->fresh(),
                        $changedFields,
                        $originalValues
                    );
                }

                return true;
            }

            DB::rollBack();

            return false;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function updateCustomerStatus(int $customerId, int $status): bool
    {
        $validate = Validator::make([
            'customer_id' => $customerId,
            'status' => $status,
        ], [
            'customer_id' => 'required|exists:customers,id',
            'status' => 'required|in:0,1',
        ]);

        if ($validate->fails()) {
            throw new \InvalidArgumentException($validate->errors()->first());
        }

        DB::beginTransaction();

        try {
            $updated = $this->customerRepository->updateStatus($customerId, $status);

            if ($updated) {
                DB::commit();

                return true;
            }

            DB::rollBack();

            return false;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function deleteCustomer(Customer $customer): bool
    {
        DB::beginTransaction();

        try {
            $deleted = $this->customerRepository->delete($customer->id);

            if ($deleted) {
                DB::commit();

                return true;
            }

            DB::rollBack();

            return false;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function getActiveCustomersForSelection(): Collection
    {
        return $this->customerRepository->getActive();
    }

    public function getCustomersByFamily(int $familyGroupId): Collection
    {
        return $this->customerRepository->getByFamilyGroup($familyGroupId);
    }

    public function getCustomersByType(string $type): Collection
    {
        return $this->customerRepository->getByType($type);
    }

    public function searchCustomers(string $query): Collection
    {
        return $this->customerRepository->search($query);
    }

    public function getCustomerStatistics(): array
    {
        $total = $this->customerRepository->count();
        $active = $this->customerRepository->getByType('Retail')->where('status', 1)->count();
        $corporate = $this->customerRepository->getByType('Corporate')->count();

        return [
            'total' => $total,
            'active' => $active,
            'corporate' => $corporate,
        ];
    }

    public function customerExists(int $customerId): bool
    {
        return $this->customerRepository->exists($customerId);
    }

    public function findByEmail(string $email): ?Customer
    {
        return $this->customerRepository->findByEmail($email);
    }

    public function findByMobileNumber(string $mobileNumber): ?Customer
    {
        return $this->customerRepository->findByMobileNumber($mobileNumber);
    }

    public function sendOnboardingMessage(Customer $customer): bool
    {
        try {
            $message = $this->generateOnboardingMessage($customer);

            return $this->whatsAppSendMessage($message, $customer->mobile_number);
        } catch (\Throwable $th) {
            \Log::warning('Failed to send onboarding WhatsApp message', [
                'customer_id' => $customer->id,
                'error' => $th->getMessage(),
            ]);

            return false;
        }
    }

    private function handleCustomerDocuments(StoreCustomerRequest|UpdateCustomerRequest $request, Customer $customer): void
    {
        $documentsUpdated = false;

        if ($request->hasFile('pan_card_path')) {
            $customer->pan_card_path = $this->fileUploadService->uploadCustomerDocument(
                $request->file('pan_card_path'),
                $customer->id,
                'pan_card',
                $customer->name
            );
            $documentsUpdated = true;
        }

        if ($request->hasFile('aadhar_card_path')) {
            $customer->aadhar_card_path = $this->fileUploadService->uploadCustomerDocument(
                $request->file('aadhar_card_path'),
                $customer->id,
                'aadhar_card',
                $customer->name
            );
            $documentsUpdated = true;
        }

        if ($request->hasFile('gst_path')) {
            $customer->gst_path = $this->fileUploadService->uploadCustomerDocument(
                $request->file('gst_path'),
                $customer->id,
                'gst',
                $customer->name
            );
            $documentsUpdated = true;
        }

        if ($documentsUpdated) {
            $customer->save();
        }
    }

    private function generateOnboardingMessage(Customer $customer): string
    {
        return $this->newCustomerAdd($customer);
    }
}
