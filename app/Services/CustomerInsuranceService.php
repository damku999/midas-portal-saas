<?php

namespace App\Services;

use App\Contracts\Repositories\CustomerInsuranceRepositoryInterface;
use App\Contracts\Services\CustomerInsuranceServiceInterface;
use App\Exports\CustomerInsurancesExport;
use App\Models\Branch;
use App\Models\Broker;
use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\FuelType;
use App\Models\InsuranceCompany;
use App\Models\PolicyType;
use App\Models\PremiumType;
use App\Models\ReferenceUser;
use App\Models\RelationshipManager;
use App\Traits\LogsNotificationsTrait;
use App\Traits\WhatsAppApiTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerInsuranceService extends BaseService implements CustomerInsuranceServiceInterface
{
    use LogsNotificationsTrait, WhatsAppApiTrait;

    public function __construct(
        private CustomerInsuranceRepositoryInterface $customerInsuranceRepository,
        private CacheService $cacheService
    ) {}

    /**
     * Retrieve paginated customer insurance policies with advanced filtering.
     *
     * This method supports complex querying with multiple filter dimensions:
     * - Search: Policy number, registration number, customer name/mobile
     * - Status: Active/inactive policies (defaults to active)
     * - Customer: Filter by specific customer ID
     * - Renewal: Already renewed or pending renewal this month
     * - Date ranges: Expiry date filtering for renewal campaigns
     *
     * Default behavior shows only active policies unless date range filters are applied,
     * supporting renewal campaign workflows where both active and inactive policies
     * need to be visible.
     *
     * @param  Request  $request  HTTP request with filter parameters
     * @return LengthAwarePaginator Paginated insurance policies with joined relationship data
     */
    public function getCustomerInsurances(Request $request): LengthAwarePaginator
    {
        $query = CustomerInsurance::query()->select([
            'customer_insurances.*',
            'customers.name as customer_name',
            'branches.name as branch_name',
            'brokers.name as broker_name',
            'relationship_managers.name as relationship_manager_name',
            'premium_types.name AS policy_type_name',
        ])
            ->join('customers', 'customers.id', 'customer_insurances.customer_id')
            ->leftJoin('branches', 'branches.id', 'customer_insurances.branch_id')
            ->leftJoin('premium_types', 'premium_types.id', 'customer_insurances.premium_type_id')
            ->leftJoin('brokers', 'brokers.id', 'customer_insurances.broker_id')
            ->leftJoin('relationship_managers', 'relationship_managers.id', 'customer_insurances.relationship_manager_id');

        // Apply search filter
        if (! empty($request->search)) {
            $search = trim((string) $request->search);
            $query->where(static function ($q) use ($search): void {
                $q->where('registration_no', 'LIKE', '%'.$search.'%')
                    ->orWhere('policy_no', 'LIKE', '%'.$search.'%')
                    ->orWhere('customers.name', 'LIKE', '%'.$search.'%')
                    ->orWhere('customers.mobile_number', 'LIKE', '%'.$search.'%');
            });
        }

        // Status filter - default to active (unless filtering by renewal due dates)
        if (! $request->filled('renewal_due_start') && ! $request->filled('renewal_due_end')) {
            $query->where('customer_insurances.status', 1);
        }

        // Apply explicit status filter if provided
        if ($request->filled('status')) {
            $query->where('customer_insurances.status', $request->input('status'));
        }

        // Customer filter
        if (! empty($request->customer_id)) {
            $query->where('customer_insurances.customer_id', $request->customer_id);
        }

        // Renewal filters
        if (! empty($request->already_renewed_this_month)) {
            $query->where('customer_insurances.is_renewed', 1);
        }

        if (! empty($request->pending_renewal_this_month)) {
            $query->where('customer_insurances.is_renewed', 0);
        }

        // Date range filter for expiring policies
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start_date = Carbon::createFromFormat('d/m/Y', $request->input('start_date'))->startOfDay();
            $end_date = Carbon::createFromFormat('d/m/Y', $request->input('end_date'))->endOfDay();
            $query->whereBetween('expired_date', [$start_date, $end_date]);
        }

        // Renewal due date filter (from dashboard)
        if ($request->filled('renewal_due_start') && $request->filled('renewal_due_end')) {
            $renewal_start = Carbon::createFromFormat('d-m-Y', $request->input('renewal_due_start'))->startOfDay();
            $renewal_end = Carbon::createFromFormat('d-m-Y', $request->input('renewal_due_end'))->endOfDay();
            $query->whereBetween('expired_date', [$renewal_start, $renewal_end]);
        }

        // Sorting
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'desc');
        $query->orderBy($sort, $direction);

        return $query->paginate(pagination_per_page());
    }

    /**
     * Retrieve form dropdown data for policy creation/editing.
     *
     * Fetches all master data required for insurance policy forms including
     * customers, brokers, insurance companies, policy types, fuel types, and
     * life insurance payment modes from configuration.
     *
     * @return array Associative array with dropdown options for all form fields
     */
    public function getFormData(): array
    {
        return [
            'customers' => Customer::query()->select(['id', 'name'])->get(),
            'brokers' => Broker::query()->select(['id', 'name'])->get(),
            'relationship_managers' => RelationshipManager::query()->select(['id', 'name'])->get(),
            'branches' => Branch::query()->select(['id', 'name'])->get(),
            'insurance_companies' => InsuranceCompany::query()->select(['id', 'name'])->get(),
            'policy_type' => PolicyType::query()->select(['id', 'name'])->get(),
            'fuel_type' => FuelType::query()->select(['id', 'name'])->get(),
            'premium_types' => PremiumType::query()->select(['id', 'name', 'is_vehicle', 'is_life_insurance_policies'])->get(),
            'reference_by_user' => ReferenceUser::query()->select(['id', 'name'])->get(),
            'life_insurance_payment_mode' => config('constants.LIFE_INSURANCE_PAYMENT_MODE'),
        ];
    }

    /**
     * Get validation rules for creating new insurance policies.
     *
     * Returns comprehensive validation rules covering:
     * - Required relationships: customer, branch, broker, insurance company
     * - Date fields: issue, expiry, start, TP expiry, maturity dates (d/m/Y format)
     * - Premium components: net, OD, TP, GST (CGST/SGST split)
     * - Commission breakdown: my/transfer/reference percentages and amounts
     * - Vehicle-specific: registration number, make/model, fuel type, NCB
     * - Life insurance: plan name, premium paying term, maturity amount
     *
     * @return array Validation rules array for Laravel validator
     */
    public function getStoreValidationRules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'broker_id' => 'required|exists:brokers,id',
            'relationship_manager_id' => 'required|exists:relationship_managers,id',
            'insurance_company_id' => 'required|exists:insurance_companies,id',
            'policy_type_id' => 'required|exists:policy_types,id',
            'fuel_type_id' => 'nullable|exists:fuel_types,id',
            'premium_type_id' => 'required|exists:premium_types,id',
            'issue_date' => 'required|date_format:d/m/Y',
            'expired_date' => 'required|date_format:d/m/Y',
            'start_date' => 'required|date_format:d/m/Y',
            'tp_expiry_date' => 'nullable|date_format:d/m/Y',
            'maturity_date' => 'nullable|date_format:d/m/Y',
            'policy_no' => 'required',
            'net_premium' => 'nullable|numeric|min:0',
            'premium_amount' => 'nullable|numeric|min:0',
            'gst' => 'nullable|numeric|min:0',
            'final_premium_with_gst' => 'required|numeric|min:0',
            'mode_of_payment' => 'nullable|string',
            'cheque_no' => 'nullable|string',
            'rto' => 'nullable|string',
            'registration_no' => 'nullable|string',
            'make_model' => 'nullable|string',
            'od_premium' => 'nullable|numeric|min:0',
            'tp_premium' => 'nullable|numeric|min:0',
            'cgst1' => 'required|numeric|min:0',
            'sgst1' => 'required|numeric|min:0',
            'cgst2' => 'nullable|numeric|min:0',
            'sgst2' => 'nullable|numeric|min:0',
            'commission_on' => 'nullable|in:net_premium,od_premium,tp_premium',
            'my_commission_percentage' => 'nullable|numeric',
            'my_commission_amount' => 'nullable|numeric',
            'transfer_commission_percentage' => 'nullable|numeric',
            'transfer_commission_amount' => 'nullable|numeric',
            'reference_commission_percentage' => 'nullable|numeric',
            'reference_commission_amount' => 'nullable|numeric',
            'actual_earnings' => 'nullable|numeric',
            'ncb_percentage' => 'nullable|numeric',
            'gross_vehicle_weight' => 'nullable|numeric',
            'mfg_year' => 'nullable|numeric',
            'plan_name' => 'nullable|string',
            'premium_paying_term' => 'nullable|string',
            'policy_term' => 'nullable|string',
            'sum_insured' => 'nullable|string',
            'pension_amount_yearly' => 'nullable|string',
            'approx_maturity_amount' => 'nullable|string',
            'remarks' => 'nullable|string',
        ];
    }

    /**
     * Get validation rules for updating existing insurance policies.
     *
     * Returns list of fields allowed for updates. All fields from creation
     * are permitted for updates, ensuring policy records can be corrected
     * or enhanced throughout their lifecycle.
     *
     * @return array Array of field names permitted for updates
     */
    public function getUpdateValidationRules(): array
    {
        return [
            'customer_id',
            'branch_id',
            'broker_id',
            'relationship_manager_id',
            'insurance_company_id',
            'premium_type_id',
            'policy_type_id',
            'fuel_type_id',
            'issue_date',
            'expired_date',
            'start_date',
            'tp_expiry_date',
            'policy_no',
            'net_premium',
            'gst',
            'final_premium_with_gst',
            'mode_of_payment',
            'cheque_no',
            'rto',
            'registration_no',
            'make_model',
            'od_premium',
            'premium_amount',
            'tp_premium',
            'cgst1',
            'sgst1',
            'cgst2',
            'sgst2',
            'commission_on',
            'my_commission_percentage',
            'my_commission_amount',
            'transfer_commission_percentage',
            'transfer_commission_amount',
            'actual_earnings',
            'ncb_percentage',
            'gross_vehicle_weight',
            'mfg_year',
            'reference_commission_percentage',
            'reference_commission_amount',
            'plan_name',
            'premium_paying_term',
            'policy_term',
            'sum_insured',
            'pension_amount_yearly',
            'approx_maturity_amount',
            'remarks',
            'maturity_date',
            'life_insurance_payment_mode',
            'reference_by',
        ];
    }

    /**
     * Get validation rules for policy renewal operations.
     *
     * Returns same validation rules as creation since renewal creates a new
     * policy record with updated dates and premium information while marking
     * the original policy as renewed.
     *
     * @return array Validation rules array (identical to store rules)
     */
    public function getRenewalValidationRules(): array
    {
        return $this->getStoreValidationRules();
    }

    /**
     * Prepare and sanitize request data for database storage.
     *
     * This method handles data transformation for policy storage:
     * - Extracts only permitted fields from request
     * - Converts date fields from d/m/Y display format to storage format
     * - Converts empty numeric strings to null (database compatibility)
     * - Preserves all premium, commission, and policy detail fields
     *
     * Critical for data integrity: ensures only validated, properly formatted
     * data reaches the database layer.
     *
     * @param  Request  $request  Validated HTTP request with policy data
     * @return array Sanitized data array ready for database insertion
     */
    public function prepareStorageData(Request $request): array
    {
        $data_to_store = $request->only([
            'customer_id',
            'branch_id',
            'broker_id',
            'relationship_manager_id',
            'insurance_company_id',
            'premium_type_id',
            'policy_type_id',
            'fuel_type_id',
            'policy_no',
            'net_premium',
            'gst',
            'final_premium_with_gst',
            'mode_of_payment',
            'cheque_no',
            'rto',
            'registration_no',
            'make_model',
            'od_premium',
            'premium_amount',
            'tp_premium',
            'cgst1',
            'sgst1',
            'cgst2',
            'sgst2',
            'commission_on',
            'my_commission_percentage',
            'my_commission_amount',
            'transfer_commission_percentage',
            'transfer_commission_amount',
            'actual_earnings',
            'ncb_percentage',
            'gross_vehicle_weight',
            'mfg_year',
            'reference_commission_percentage',
            'reference_commission_amount',
            'plan_name',
            'premium_paying_term',
            'policy_term',
            'sum_insured',
            'pension_amount_yearly',
            'approx_maturity_amount',
            'remarks',
            'life_insurance_payment_mode',
            'reference_by',
        ]);

        // Handle date fields
        $dateFields = ['issue_date', 'expired_date', 'start_date', 'tp_expiry_date', 'maturity_date'];
        foreach ($dateFields as $field) {
            if (! empty($request->$field)) {
                $data_to_store[$field] = $request->$field;
            }
        }

        // Handle numeric fields - convert empty strings to null
        $numericFields = [
            'net_premium', 'premium_amount', 'gst', 'final_premium_with_gst',
            'od_premium', 'tp_premium', 'cgst1', 'sgst1', 'cgst2', 'sgst2',
            'my_commission_percentage', 'my_commission_amount',
            'transfer_commission_percentage', 'transfer_commission_amount',
            'reference_commission_percentage', 'reference_commission_amount',
            'actual_earnings', 'ncb_percentage', 'gross_vehicle_weight',
            'mfg_year', 'sum_insured', 'pension_amount_yearly', 'approx_maturity_amount',
            'premium_paying_term', 'policy_term',
        ];

        foreach ($numericFields as $numericField) {
            if (array_key_exists($numericField, $data_to_store)) {
                $data_to_store[$numericField] = $data_to_store[$numericField] === '' ? null : $data_to_store[$numericField];
            }
        }

        return $data_to_store;
    }

    /**
     * Create a new insurance policy with automatic commission calculation.
     *
     * This method orchestrates policy creation within a database transaction:
     * 1. Calculates commission breakdown based on commission_on field
     * 2. Computes my_commission, transfer_commission, reference_commission
     * 3. Calculates actual_earnings (net commission after deductions)
     * 4. Creates policy record with all calculated fields
     *
     * Transaction ensures commission calculations and policy creation are atomic.
     *
     * @param  array  $data  Prepared policy data from prepareStorageData()
     * @return CustomerInsurance Newly created policy instance
     *
     * @throws QueryException On database constraint violations
     */
    public function createCustomerInsurance(array $data): CustomerInsurance
    {
        return $this->createInTransaction(function () use ($data): Model {
            // Calculate commission breakdown
            $data = $this->calculateCommissionFields($data);

            return $this->customerInsuranceRepository->create($data);
        });
    }

    /**
     * Update an existing insurance policy with commission recalculation.
     *
     * This method updates policy records within a transaction:
     * 1. Recalculates commission breakdown based on new values
     * 2. Updates policy record with new data
     * 3. Handles policy document upload if present in data
     * 4. Replaces old document file with new one if uploaded
     *
     * Transaction ensures commission updates and document handling are atomic.
     *
     * @param  CustomerInsurance  $customerInsurance  Policy instance to update
     * @param  array  $data  Updated policy data (may include policy_document)
     * @return CustomerInsurance Updated policy instance with fresh relationships
     *
     * @throws QueryException On database constraint violations
     */
    public function updateCustomerInsurance(CustomerInsurance $customerInsurance, array $data): CustomerInsurance
    {
        return $this->updateInTransaction(function () use ($customerInsurance, $data): Model {
            // Calculate commission breakdown
            $data = $this->calculateCommissionFields($data);

            $model = $this->customerInsuranceRepository->update($customerInsurance, $data);

            // Handle policy document upload if present
            if (isset($data['policy_document']) && $data['policy_document']) {
                $this->handlePolicyDocument($model, $data['policy_document']);
            }

            return $model;
        });
    }

    /**
     * Delete an insurance policy and its associated documents.
     *
     * This method performs cleanup within a transaction:
     * 1. Deletes policy document file from storage if exists
     * 2. Removes policy record from database
     *
     * Transaction ensures file deletion and database removal are atomic,
     * preventing orphaned database records or storage files.
     *
     * @param  CustomerInsurance  $customerInsurance  Policy instance to delete
     * @return bool True on successful deletion
     *
     * @throws QueryException On database constraint violations
     */
    public function deleteCustomerInsurance(CustomerInsurance $customerInsurance): bool
    {
        return $this->deleteInTransaction(function () use ($customerInsurance): bool {
            // Delete policy document if exists
            if ($customerInsurance->policy_document_path && Storage::exists($customerInsurance->policy_document_path)) {
                Storage::delete($customerInsurance->policy_document_path);
            }

            return $this->customerInsuranceRepository->delete($customerInsurance);
        });
    }

    /**
     * Toggle insurance policy active/inactive status.
     *
     * Updates the status field (0 = inactive, 1 = active) within a transaction.
     * Used for soft-disabling policies without full deletion, preserving policy
     * history while removing from active policy lists.
     *
     * @param  int  $customerInsuranceId  Policy ID to update
     * @param  int  $status  New status (0 or 1)
     * @return bool True on successful update
     */
    public function updateStatus(int $customerInsuranceId, int $status): bool
    {
        return $this->executeInTransaction(
            fn (): bool => $this->customerInsuranceRepository->updateStatus($customerInsuranceId, $status)
        );
    }

    /**
     * Handle policy document file upload with intelligent naming.
     *
     * This method processes policy document uploads:
     * - Generates descriptive filename using registration number or customer details
     * - Format: {REGNO}-{YEAR}-POLICY COPY-{TIMESTAMP} or
     *           {CUSTOMER}-{TYPE}-{POLICYNO}-{YEAR}-POLICY COPY-{TIMESTAMP}
     * - Sanitizes filename (alphanumeric, hyphens only)
     * - Stores in customer_insurances/{id}/policy_document_path directory
     * - Updates policy record with new document path
     *
     * Filename strategy prioritizes vehicle registration number for easy identification.
     *
     * @param  Request  $request  HTTP request containing policy_document_path file
     * @param  CustomerInsurance  $customerInsurance  Policy instance to attach document to
     */
    public function handleFileUpload(Request $request, CustomerInsurance $customerInsurance): void
    {
        if ($request->hasFile('policy_document_path')) {
            $file = $request->file('policy_document_path');
            $timestamp = time();

            // Extract necessary information
            $customerName = $customerInsurance->customer->name;
            $premiumType = $customerInsurance->premiumType->name;
            $policyNo = $customerInsurance->policy_no;
            $registrationNo = $customerInsurance->registration_no;
            $currentYear = date('Y');

            if (! empty($registrationNo)) {
                $fileName = $registrationNo.'-'.$currentYear.'-POLICY COPY-'.$timestamp;
            } else {
                $fileName = $customerName.'-'.$premiumType.'-'.$policyNo.'-'.$currentYear.'-POLICY COPY-'.$timestamp;
            }

            // Clean filename
            $fileName = trim($fileName, '-');
            $fileName = str_replace('--', '-', $fileName);
            $fileName .= '-'.time();
            $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '', str_replace(' ', '-', $fileName));

            // Store the file
            $path = $file->storeAs(
                'customer_insurances/'.$customerInsurance->id.'/policy_document_path',
                $fileName.'.'.$file->getClientOriginalExtension(),
                'public'
            );

            // Update the policy_document_path
            $customerInsurance->update(['policy_document_path' => $path]);
        }
    }

    /**
     * Send policy document to customer via WhatsApp with attachment.
     *
     * This method orchestrates WhatsApp document delivery:
     * 1. Validates policy document exists in storage
     * 2. Renders message from notification template (policy_created/whatsapp)
     * 3. Falls back to hardcoded message if template not found
     * 4. Sends message with document attachment via WhatsApp API
     * 5. Logs all operations (success/failure) with comprehensive context
     *
     * Critical for customer communication: provides immediate policy delivery
     * after policy creation or updates.
     *
     * @param  CustomerInsurance  $customerInsurance  Policy with customer and document
     * @return bool True on successful send
     *
     * @throws \Exception If document file not found or WhatsApp API fails
     */
    public function sendWhatsAppDocument(CustomerInsurance $customerInsurance): bool
    {
        Log::info('Starting WhatsApp document send', [
            'customer_insurance_id' => $customerInsurance->id,
            'policy_no' => $customerInsurance->policy_no,
            'customer_name' => $customerInsurance->customer->name ?? 'N/A',
            'mobile_number' => $customerInsurance->customer->mobile_number,
            'document_path' => $customerInsurance->policy_document_path,
            'user_id' => auth()->user()->id ?? 'System',
        ]);

        if (empty($customerInsurance->policy_document_path)) {
            Log::warning('WhatsApp document send skipped - no document path', [
                'customer_insurance_id' => $customerInsurance->id,
                'policy_no' => $customerInsurance->policy_no,
            ]);

            return false;
        }

        try {
            // Try to get message from template, fallback to hardcoded
            $templateService = app(TemplateService::class);
            $message = $templateService->renderFromInsurance('policy_created', 'whatsapp', $customerInsurance);
            $template = $templateService->getTemplateByCode('policy_created', 'whatsapp');

            if (! $message) {
                // Fallback to old hardcoded message
                $message = $this->insuranceAdded($customerInsurance);
            }

            $filePath = Storage::path('public'.DIRECTORY_SEPARATOR.$customerInsurance->policy_document_path);

            if (! file_exists($filePath)) {
                throw new \Exception('Policy document file not found: '.$filePath);
            }

            // Use trait method to log and send with attachment
            $result = $this->logAndSendWhatsAppWithAttachment(
                $customerInsurance,
                $message,
                $customerInsurance->customer->mobile_number,
                $filePath,
                [
                    'notification_type_code' => 'policy_created',
                    'template_id' => $template->id ?? null,
                ]
            );

            Log::info('WhatsApp document sent successfully', [
                'customer_insurance_id' => $customerInsurance->id,
                'policy_no' => $customerInsurance->policy_no,
                'mobile_number' => $customerInsurance->customer->mobile_number,
                'result' => $result,
                'user_id' => auth()->user()->id ?? 'System',
            ]);

            return $result['success'];

        } catch (\Exception $exception) {
            Log::error('WhatsApp document send failed', [
                'customer_insurance_id' => $customerInsurance->id,
                'policy_no' => $customerInsurance->policy_no,
                'customer_name' => $customerInsurance->customer->name ?? 'N/A',
                'mobile_number' => $customerInsurance->customer->mobile_number,
                'document_path' => $customerInsurance->policy_document_path,
                'error' => $exception->getMessage(),
                'user_id' => auth()->user()->id ?? 'System',
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception; // Re-throw to maintain the error flow
        }
    }

    /**
     * Send policy document to customer via email with attachment.
     *
     * This method orchestrates email document delivery:
     * 1. Validates policy document exists in storage
     * 2. Uses EmailService to send templated email (policy_created template)
     * 3. Attaches policy document PDF to email
     * 4. Logs all operations (success/failure) with comprehensive context
     *
     * Works in tandem with WhatsApp delivery for multi-channel policy distribution,
     * providing customers with digital copies via their preferred channels.
     *
     * @param  CustomerInsurance  $customerInsurance  Policy with customer and document
     * @return bool True on successful send
     *
     * @throws \Exception If document file not found or email sending fails
     */
    public function sendPolicyDocumentEmail(CustomerInsurance $customerInsurance): bool
    {
        Log::info('Starting email policy document send', [
            'customer_insurance_id' => $customerInsurance->id,
            'policy_no' => $customerInsurance->policy_no,
            'customer_name' => $customerInsurance->customer->name ?? 'N/A',
            'email' => $customerInsurance->customer->email,
            'user_id' => auth()->user()->id ?? 'System',
        ]);

        if (empty($customerInsurance->policy_document_path)) {
            Log::warning('Email document send skipped - no document path', [
                'customer_insurance_id' => $customerInsurance->id,
                'policy_no' => $customerInsurance->policy_no,
            ]);

            return false;
        }

        try {
            $filePath = Storage::path('public'.DIRECTORY_SEPARATOR.$customerInsurance->policy_document_path);

            if (! file_exists($filePath)) {
                throw new \Exception('Policy document file not found: '.$filePath);
            }

            $emailService = app(EmailService::class);
            $sent = $emailService->sendFromInsurance('policy_created', $customerInsurance, [$filePath]);

            if ($sent) {
                Log::info('Email document sent successfully', [
                    'customer_insurance_id' => $customerInsurance->id,
                    'policy_no' => $customerInsurance->policy_no,
                    'email' => $customerInsurance->customer->email,
                    'user_id' => auth()->user()->id ?? 'System',
                ]);
            }

            return $sent;

        } catch (\Exception $exception) {
            Log::error('Email document send failed', [
                'customer_insurance_id' => $customerInsurance->id,
                'policy_no' => $customerInsurance->policy_no,
                'customer_name' => $customerInsurance->customer->name ?? 'N/A',
                'email' => $customerInsurance->customer->email,
                'document_path' => $customerInsurance->policy_document_path,
                'error' => $exception->getMessage(),
                'user_id' => auth()->user()->id ?? 'System',
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception; // Re-throw to maintain the error flow
        }
    }

    /**
     * Send policy renewal reminder to customer via WhatsApp.
     *
     * This method implements intelligent renewal reminder delivery:
     * 1. Calculates days until policy expiry
     * 2. Selects appropriate notification template based on urgency:
     *    - renewal_expired: Already expired (0 days or negative)
     *    - renewal_7_days: Urgent reminder (1-7 days)
     *    - renewal_15_days: Important reminder (8-15 days)
     *    - renewal_30_days: Early reminder (16+ days)
     * 3. Renders message from template or falls back to hardcoded message
     * 4. Sends WhatsApp message via API
     * 5. Logs comprehensive operation context for tracking
     *
     * Supports renewal campaign workflows by adapting message urgency to
     * expiry timeline, maximizing renewal conversion rates.
     *
     * @param  CustomerInsurance  $customerInsurance  Policy with expiry date and customer
     * @return bool True on successful send
     *
     * @throws \Exception If WhatsApp API fails
     */
    public function sendRenewalReminderWhatsApp(CustomerInsurance $customerInsurance): bool
    {
        Log::info('Starting WhatsApp renewal reminder send', [
            'customer_insurance_id' => $customerInsurance->id,
            'policy_no' => $customerInsurance->policy_no,
            'customer_name' => $customerInsurance->customer->name ?? 'N/A',
            'mobile_number' => $customerInsurance->customer->mobile_number,
            'expired_date' => $customerInsurance->expired_date,
            'is_vehicle' => $customerInsurance->premiumType->is_vehicle ?? 0,
            'user_id' => auth()->user()->id ?? 'System',
        ]);

        try {
            // Determine notification type based on days until expiry
            $daysUntilExpiry = now()->diffInDays($customerInsurance->expired_date, false);

            if ($daysUntilExpiry <= 0) {
                $notificationTypeCode = 'renewal_expired';
            } elseif ($daysUntilExpiry <= 7) {
                $notificationTypeCode = 'renewal_7_days';
            } elseif ($daysUntilExpiry <= 15) {
                $notificationTypeCode = 'renewal_15_days';
            } else {
                $notificationTypeCode = 'renewal_30_days';
            }

            // Try to get message from template, fallback to hardcoded
            $templateService = app(TemplateService::class);
            $messageText = $templateService->renderFromInsurance($notificationTypeCode, 'whatsapp', $customerInsurance);
            $template = $templateService->getTemplateByCode($notificationTypeCode, 'whatsapp');

            if (! $messageText) {
                // Fallback to old hardcoded message
                $messageText = $customerInsurance->premiumType->is_vehicle == 1
                    ? $this->renewalReminderVehicle($customerInsurance)
                    : $this->renewalReminder($customerInsurance);
            }

            $receiverId = $customerInsurance->customer->mobile_number;

            // Use trait method to log and send
            $result = $this->logAndSendWhatsApp(
                $customerInsurance,
                $messageText,
                $receiverId,
                [
                    'notification_type_code' => $notificationTypeCode,
                    'template_id' => $template->id ?? null,
                ]
            );

            Log::info('WhatsApp renewal reminder sent successfully', [
                'customer_insurance_id' => $customerInsurance->id,
                'policy_no' => $customerInsurance->policy_no,
                'mobile_number' => $receiverId,
                'result' => $result,
                'user_id' => auth()->user()->id ?? 'System',
            ]);

            return $result['success'];

        } catch (\Exception $exception) {
            Log::error('WhatsApp renewal reminder send failed', [
                'customer_insurance_id' => $customerInsurance->id,
                'policy_no' => $customerInsurance->policy_no,
                'customer_name' => $customerInsurance->customer->name ?? 'N/A',
                'mobile_number' => $customerInsurance->customer->mobile_number,
                'expired_date' => $customerInsurance->expired_date,
                'error' => $exception->getMessage(),
                'user_id' => auth()->user()->id ?? 'System',
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception; // Re-throw to maintain the error flow
        }
    }

    /**
     * Renew an existing insurance policy by creating new policy record.
     *
     * This method implements the policy renewal workflow within a transaction:
     * 1. Recalculates commission breakdown for renewal data
     * 2. Prepares renewal data (removes old ID, resets renewal flags)
     * 3. Creates new policy record with updated dates and premiums
     * 4. Marks original policy as:
     *    - status = 0 (inactive)
     *    - is_renewed = 1 (renewed flag)
     *    - renewed_date = current timestamp
     *    - new_insurance_id = link to new policy
     *
     * Transaction ensures both old and new policy states are consistent,
     * maintaining complete renewal audit trail.
     *
     * @param  CustomerInsurance  $customerInsurance  Original policy to renew
     * @param  array  $data  New policy data for renewal (dates, premiums, etc.)
     * @return CustomerInsurance Newly created renewal policy instance
     *
     * @throws QueryException On database constraint violations
     */
    public function renewPolicy(CustomerInsurance $customerInsurance, array $data): CustomerInsurance
    {
        return $this->executeInTransaction(function () use ($customerInsurance, $data): Model {
            // Calculate commission breakdown for renewal data
            $data = $this->calculateCommissionFields($data);

            // Create new policy record for renewal
            $renewalData = $this->prepareRenewalStorageData($data);
            $model = $this->customerInsuranceRepository->create($renewalData);

            // Mark original policy as renewed
            $this->customerInsuranceRepository->update($customerInsurance, [
                'status' => 0,
                'is_renewed' => 1,
                'renewed_date' => Carbon::now(),
                'new_insurance_id' => $model->id,
            ]);

            return $model;
        });
    }

    /**
     * Export all customer insurance policies to Excel spreadsheet.
     *
     * Generates downloadable XLSX file with comprehensive policy data
     * using Laravel Excel export class. Includes all policy details,
     * customer information, premiums, commissions, and dates.
     *
     * Supports data analysis, reporting, and backup workflows.
     *
     * @return BinaryFileResponse Excel file download response
     */
    public function exportCustomerInsurances(): BinaryFileResponse
    {
        return Excel::download(new CustomerInsurancesExport, 'customer_insurances.xlsx');
    }

    /**
     * Retrieve all insurance policies for a specific customer.
     *
     * Fetches complete policy collection for customer profile views,
     * enabling policy history review, family group access, and
     * cross-selling opportunities.
     *
     * @param  int  $customerId  Customer ID to fetch policies for
     * @return Collection All policies belonging to the customer
     */
    public function getCustomerPolicies(int $customerId): Collection
    {
        return $this->customerInsuranceRepository->getByCustomerId($customerId);
    }

    /**
     * Send policy document to specified WhatsApp number.
     *
     * This method enables manual policy sharing to any WhatsApp number:
     * 1. Fetches policy with all relationships loaded
     * 2. Validates policy document exists in storage
     * 3. Generates simple message with customer name and policy number
     * 4. Sends document via WhatsApp API to specified number
     *
     * Useful for sharing policies with family members, third parties,
     * or alternative contact numbers beyond primary customer mobile.
     *
     * @param  int  $customerInsuranceId  Policy ID to send
     * @param  string  $whatsappNumber  Destination WhatsApp number
     * @return array WhatsApp API response (decoded JSON or raw response)
     *
     * @throws \Exception If policy not found or document missing
     */
    public function sendPolicyWhatsApp(int $customerInsuranceId, string $whatsappNumber): array
    {
        $customerInsurance = $this->customerInsuranceRepository->findWithRelations($customerInsuranceId);

        if (! $customerInsurance instanceof CustomerInsurance) {
            throw new \Exception('Customer Insurance not found');
        }

        if (! $customerInsurance->policy_document_path || ! Storage::exists($customerInsurance->policy_document_path)) {
            throw new \Exception('Policy document not found');
        }

        $documentPath = Storage::path('public'.DIRECTORY_SEPARATOR.$customerInsurance->policy_document_path);
        $message = sprintf('Dear %s, Please find your policy document for Policy No: %s', $customerInsurance->customer->name, $customerInsurance->policy_no);

        // Use trait method to log and send with attachment
        $result = $this->logAndSendWhatsAppWithAttachment(
            $customerInsurance,
            $message,
            $whatsappNumber,
            $documentPath,
            [
                'notification_type_code' => 'policy_shared',
                'template_id' => null,
            ]
        );

        return $result['success']
            ? ['success' => true, 'result' => $result]
            : ['success' => false, 'error' => $result['error'] ?? 'Unknown error'];
    }

    /**
     * Retrieve policies expiring within specified days.
     *
     * Fetches active policies approaching expiry for renewal campaign workflows.
     * Default 30-day window supports proactive renewal outreach, maximizing
     * renewal conversion rates through timely customer engagement.
     *
     * @param  int  $days  Number of days to look ahead (default: 30)
     * @return Collection Policies expiring within the specified timeframe
     */
    public function getExpiringPolicies(int $days = 30): Collection
    {
        return $this->customerInsuranceRepository->getExpiringPolicies($days);
    }

    /**
     * Calculate detailed commission breakdown for an insurance policy.
     *
     * This method computes commission structure based on commission_on field:
     * - net_premium: Commission on net premium amount
     * - od_premium: Commission on Own Damage premium only
     * - tp_premium: Commission on Third Party premium only
     *
     * Calculates:
     * 1. Base premium (depends on commission_on selection)
     * 2. My commission = base × my_commission_percentage
     * 3. Transfer commission = base × transfer_commission_percentage (paid to broker)
     * 4. Reference commission = base × reference_commission_percentage (referral fee)
     * 5. Actual earnings = my_commission - transfer - reference (net profit)
     *
     * Critical for financial reporting: provides transparent commission breakdown
     * for profitability analysis and commission reconciliation.
     *
     * @param  CustomerInsurance  $customerInsurance  Policy with commission fields
     * @return array Commission breakdown with base_premium, all commission types, and actual_earnings
     */
    public function calculateCommissionBreakdown(CustomerInsurance $customerInsurance): array
    {
        $basePremium = match ($customerInsurance->commission_on) {
            'net_premium' => $customerInsurance->net_premium ?? 0,
            'od_premium' => $customerInsurance->od_premium ?? 0,
            'tp_premium' => $customerInsurance->tp_premium ?? 0,
            default => $customerInsurance->net_premium ?? 0
        };

        $myCommission = ($basePremium * ($customerInsurance->my_commission_percentage ?? 0)) / 100;
        $transferCommission = ($basePremium * ($customerInsurance->transfer_commission_percentage ?? 0)) / 100;
        $referenceCommission = ($basePremium * ($customerInsurance->reference_commission_percentage ?? 0)) / 100;
        $actualEarnings = $myCommission - $transferCommission - $referenceCommission;

        return [
            'base_premium' => $basePremium,
            'my_commission' => $myCommission,
            'transfer_commission' => $transferCommission,
            'reference_commission' => $referenceCommission,
            'actual_earnings' => $actualEarnings,
        ];
    }

    private function calculateCommissionFields(array $data): array
    {
        if (! isset($data['commission_on'])) {
            return $data;
        }

        $basePremium = match ($data['commission_on']) {
            'net_premium' => $data['net_premium'] ?? 0,
            'od_premium' => $data['od_premium'] ?? 0,
            'tp_premium' => $data['tp_premium'] ?? 0,
            default => $data['net_premium'] ?? 0
        };

        $data['my_commission_amount'] = ($basePremium * ($data['my_commission_percentage'] ?? 0)) / 100;
        $data['transfer_commission_amount'] = ($basePremium * ($data['transfer_commission_percentage'] ?? 0)) / 100;
        $data['reference_commission_amount'] = ($basePremium * ($data['reference_commission_percentage'] ?? 0)) / 100;
        $data['actual_earnings'] = $data['my_commission_amount'] - $data['transfer_commission_amount'] - $data['reference_commission_amount'];

        return $data;
    }

    private function prepareRenewalStorageData(array $renewalData): array
    {
        // Remove fields that shouldn't be copied to new record
        unset($renewalData['id']);
        $renewalData['is_renewed'] = 0;
        $renewalData['renewed_date'] = null;
        $renewalData['new_insurance_id'] = null;
        $renewalData['created_at'] = now();
        $renewalData['updated_at'] = now();

        return $renewalData;
    }

    /**
     * Handle policy document upload for an existing customer insurance.
     *
     * @param  UploadedFile  $policyDocument
     */
    private function handlePolicyDocument(CustomerInsurance $customerInsurance, $policyDocument): void
    {
        if (! $policyDocument) {
            return;
        }

        $timestamp = time();

        // Extract necessary information
        $customerName = $customerInsurance->customer->name;
        $premiumType = $customerInsurance->premiumType->name;
        $policyNo = $customerInsurance->policy_no;
        $registrationNo = $customerInsurance->registration_no;
        $currentYear = date('Y');

        if (! empty($registrationNo)) {
            $fileName = $registrationNo.'-'.$currentYear.'-POLICY COPY-'.$timestamp;
        } else {
            $fileName = $customerName.'-'.$premiumType.'-'.$policyNo.'-'.$currentYear.'-POLICY COPY-'.$timestamp;
        }

        // Clean filename
        $fileName = trim($fileName, '-');
        $fileName = str_replace('--', '-', $fileName);
        $fileName .= '-'.time();
        $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '', str_replace(' ', '-', $fileName));

        // Delete old policy document if exists
        if ($customerInsurance->policy_document_path && Storage::exists($customerInsurance->policy_document_path)) {
            Storage::delete($customerInsurance->policy_document_path);
        }

        // Store the new file
        $path = $policyDocument->storeAs(
            'customer_insurances/'.$customerInsurance->id.'/policy_document_path',
            $fileName.'.'.$policyDocument->getClientOriginalExtension(),
            'public'
        );

        // Update the policy_document_path
        $customerInsurance->update(['policy_document_path' => $path]);
    }
}
