# Claims Management

**Version:** 1.0
**Last Updated:** 2025-11-06
**Status:** Production

## Overview

Comprehensive insurance claims processing system with document tracking, stage management, automated WhatsApp/email notifications, and liability calculations for Health and Vehicle insurance claims.

### Key Features

- **Auto-Generated Claim Numbers**: Format CLM{YYYY}{MM}{0001} - year/month based sequential
- **Insurance Type Support**: Health and Vehicle/Truck insurance with type-specific document checklists
- **Document Tracking**: 10 documents for Health, 20 for Vehicle with completion percentage tracking
- **Stage Management**: Multi-stage workflow with current stage tracking and history
- **WhatsApp Notifications**: Document requests, pending reminders, claim number, stage updates
- **Email Notifications**: Parallel email delivery with template system (opt-in per claim)
- **Liability Tracking**: Cashless/Reimbursement with amount calculations and deductions
- **Progress Indicators**: Document completion %, required document completion %, stage timeline
- **Audit Trail**: Full Spatie Activity Log integration for all claim operations

## Claim Model

**File**: `app/Models/Claim.php`

### Attributes

**Identification**
- `claim_number` (string) - Auto-generated format: CLM{YYYY}{MM}{0001} (e.g., CLM2025110001)
- `customer_id` (bigint) - Customer reference (foreign key)
- `customer_insurance_id` (bigint) - Policy reference (foreign key)

**Claim Details**
- `insurance_type` (string) - Health, Vehicle (determines document checklist)
- `incident_date` (date) - Date of incident/hospitalization
- `description` (text, nullable) - Claim description and circumstances

**Communication**
- `whatsapp_number` (string) - WhatsApp contact (defaults to customer mobile)
- `send_email_notifications` (boolean) - Enable/disable email notifications for this claim

**Status & Audit**
- `status` (boolean) - Active/Inactive (1/0)
- `created_at`, `updated_at`, `deleted_at` - Soft delete timestamps
- `created_by`, `updated_by`, `deleted_by` - User audit trail

### Relationships

```php
// Claim belongs to customer
public function customer(): BelongsTo
{
    return $this->belongsTo(Customer::class);
}

// Claim belongs to insurance policy
public function customerInsurance(): BelongsTo
{
    return $this->belongsTo(CustomerInsurance::class);
}

// Claim has many stages (ordered by created_at)
public function stages(): HasMany
{
    return $this->hasMany(ClaimStage::class)->orderBy('created_at');
}

// Claim has one current stage
public function currentStage(): HasOne
{
    return $this->hasOne(ClaimStage::class)->where('is_current', true);
}

// Claim has many documents (ordered by is_required DESC)
public function documents(): HasMany
{
    return $this->hasMany(ClaimDocument::class)->orderBy('is_required', 'desc');
}

// Claim has one liability detail
public function liabilityDetail(): HasOne
{
    return $this->hasOne(ClaimLiabilityDetail::class);
}
```

### Claim Number Generation

```php
public static function generateClaimNumber(): string
{
    $year = now()->format('Y');   // 2025
    $month = now()->format('m');  // 11

    // Get the last claim number for current month
    $lastClaim = self::query()->where('claim_number', 'like', "CLM{$year}{$month}%")
        ->orderBy('claim_number', 'desc')
        ->first();

    if ($lastClaim) {
        $lastNumber = (int) substr($lastClaim->claim_number, -4);
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '0001';
    }

    return "CLM{$year}{$month}{$newNumber}";
}

// Examples:
// CLM202511000 1 - First claim of November 2025
// CLM202511 0042 - 42nd claim of November 2025
// CLM202512000 1 - First claim of December 2025 (resets counter)
```

### Default Document Creation

**Health Insurance Documents** (10 items):
```php
public function createDefaultDocuments(): void
{
    if ($this->insurance_type === 'Health') {
        $documents = [
            ['name' => 'Patient Name', 'description' => 'Name of the patient receiving treatment', 'required' => true],
            ['name' => 'Policy No', 'description' => 'Insurance policy number', 'required' => true],
            ['name' => 'Contact no', 'description' => 'Patient contact number', 'required' => true],
            ['name' => 'Date of Admission', 'description' => 'Hospital admission date', 'required' => true],
            ['name' => 'Treating Doctor Name', 'description' => 'Name of treating physician', 'required' => true],
            ['name' => 'Hospital Name', 'description' => 'Name of the hospital', 'required' => true],
            ['name' => 'Address of Hospital', 'description' => 'Complete hospital address', 'required' => true],
            ['name' => 'Illness', 'description' => 'Nature of illness/treatment', 'required' => true],
            ['name' => 'Approx Hospitalisation Days', 'description' => 'Expected duration of stay', 'required' => true],
            ['name' => 'Approx Cost', 'description' => 'Estimated treatment cost', 'required' => true],
        ];
    }
}
```

**Vehicle Insurance Documents** (20 items):
```php
if ($this->insurance_type === 'Vehicle') {
    $documents = [
        ['name' => 'Claim form duly Signed', 'description' => 'Completed and signed claim form', 'required' => true],
        ['name' => 'Policy Copy', 'description' => 'Insurance policy document copy', 'required' => true],
        ['name' => 'RC Copy', 'description' => 'Registration Certificate copy', 'required' => true],
        ['name' => 'Driving License', 'description' => 'Valid driving license copy', 'required' => true],
        ['name' => 'Driver Contact Number', 'description' => 'Contact details of driver', 'required' => true],
        ['name' => 'Spot Location Address', 'description' => 'Exact accident location', 'required' => true],
        ['name' => 'Fitness Certificate', 'description' => 'Vehicle fitness certificate', 'required' => true],
        ['name' => 'Permit', 'description' => 'Commercial vehicle permit', 'required' => true],
        ['name' => 'Road tax', 'description' => 'Road tax receipt/certificate', 'required' => true],
        ['name' => 'Cancel Cheque', 'description' => 'Cancelled cheque for payment', 'required' => true],
        ['name' => 'Fast tag statement', 'description' => 'FASTag transaction statement', 'required' => false],
        ['name' => 'CKYC Form', 'description' => 'Central KYC form', 'required' => true],
        ['name' => 'Insured Pan and Address Proof', 'description' => 'PAN card and address proof', 'required' => true],
        ['name' => 'Load Challan', 'description' => 'Goods loading receipt/challan', 'required' => false],
        ['name' => 'All side spot Photos with driver selfie', 'description' => 'Complete accident photos', 'required' => true],
        ['name' => 'Towing Bill', 'description' => 'Vehicle towing charges receipt', 'required' => false],
        ['name' => 'Workshop Estimate', 'description' => 'Repair cost estimate from workshop', 'required' => true],
        ['name' => 'FIR - Yes or No', 'description' => 'Police FIR status and copy if filed', 'required' => false],
        ['name' => 'Third Party Injury - Yes or No', 'description' => 'Third party injury details if any', 'required' => false],
        ['name' => 'How accident Happened?', 'description' => 'Detailed accident description', 'required' => true],
    ];
}
```

### Initial Stage Creation

```php
public function createInitialStage(): ClaimStage
{
    return $this->stages()->create([
        'stage_name' => 'Claim Registered',
        'description' => 'Claim has been registered and assigned claim number',
        'is_current' => true,
        'is_completed' => true,
        'stage_date' => now(),
        'notes' => 'Initial claim registration completed',
    ]);
}
```

### Document Completion Tracking

**Overall Completion**:
```php
public function getDocumentCompletionPercentage(): float
{
    $totalDocs = $this->documents()->count();
    if ($totalDocs === 0) return 0;

    $submittedDocs = $this->documents()->where('is_submitted', true)->count();
    return round(($submittedDocs / $totalDocs) * 100, 2);
}

// Example: 15/20 documents submitted = 75.00%
```

**Required Documents Completion**:
```php
public function getRequiredDocumentCompletionPercentage(): float
{
    $totalRequiredDocs = $this->documents()->where('is_required', true)->count();
    if ($totalRequiredDocs === 0) return 100; // No required docs = 100% complete

    $submittedRequiredDocs = $this->documents()
        ->where('is_required', true)
        ->where('is_submitted', true)
        ->count();

    return round(($submittedRequiredDocs / $totalRequiredDocs) * 100, 2);
}

// Example: Vehicle claim with 14/16 required docs = 87.50%
```

### WhatsApp Notification Methods

**Document List (Initial Request)**:
```php
public function sendDocumentListWhatsApp(): array
{
    $notificationTypeCode = $this->insurance_type === 'Health'
        ? 'document_request_health'
        : 'document_request_vehicle';

    // Try template system, fallback to hardcoded message
    $templateService = app(TemplateService::class);
    $message = $templateService->renderFromClaim($notificationTypeCode, 'whatsapp', $this);

    if (!$message) {
        $message = $this->insurance_type === 'Health'
            ? $this->getHealthInsuranceDocumentListMessage()
            : $this->getVehicleInsuranceDocumentListMessage();
    }

    $response = $this->whatsAppSendMessage($message, $this->getWhatsAppNumber());

    return [
        'success' => true,
        'message' => 'Document list sent via WhatsApp successfully',
        'response' => $response,
    ];
}
```

**Pending Documents Reminder**:
```php
public function sendPendingDocumentsWhatsApp(): array
{
    $pendingDocuments = $this->documents()->where('is_submitted', false)->get();

    if ($pendingDocuments->isEmpty()) {
        return [...]; // All documents received message
    }

    $message = "Below are the Documents pending from your side, Send it urgently for hassle free claim service\n\n";

    $counter = 1;
    foreach ($pendingDocuments as $doc) {
        $message .= $counter . '. ' . $doc->document_name . "\n";
        $counter++;
    }

    $message .= "\nBest regards,\n{Company Details}";

    $response = $this->whatsAppSendMessage($message, $this->getWhatsAppNumber());

    return ['success' => true, 'message' => 'Pending documents reminder sent', ...];
}
```

**Claim Number Notification**:
```php
public function sendClaimNumberWhatsApp(): array
{
    $vehicleNumber = $this->customerInsurance->registration_no ?? 'N/A';

    $message = "Dear customer your Claim Number {$this->claim_number} is generated against your vehicle number {$vehicleNumber}. For further assistance kindly contact me.\n\n{Company Details}";

    $response = $this->whatsAppSendMessage($message, $this->getWhatsAppNumber());

    return ['success' => true, 'message' => 'Claim number sent', ...];
}
```

**Stage Update Notification**:
```php
public function sendStageUpdateWhatsApp(string $stageName, ?string $notes = null): array
{
    $message = "Dear {$this->customer->name},\n\nYour claim {$this->claim_number} status has been updated to: *{$stageName}*";

    if ($notes) {
        $message .= "\n\nNotes: {$notes}";
    }

    $message .= "\n\nFor further assistance, please contact us.\n\n{Company Details}";

    $response = $this->whatsAppSendMessage($message, $this->getWhatsAppNumber());

    return ['success' => true, 'message' => 'Stage update sent', ...];
}
```

### Email Notification Methods

**Unified Email Handler**:
```php
public function sendEmailNotification(string $type, array $additionalData = []): array
{
    // Check if email notifications enabled for this claim
    if (!$this->send_email_notifications) {
        return ['success' => true, 'message' => 'Email notifications disabled', 'sent' => false];
    }

    // Check if customer has email
    if (empty($this->customer->email)) {
        return ['success' => false, 'message' => 'No email address', 'sent' => false];
    }

    // Check global email notification setting
    if (!is_email_notification_enabled()) {
        Log::info('Email notification skipped (disabled in settings)', [
            'claim_id' => $this->id,
            'type' => $type,
        ]);
        return ['success' => false, 'message' => 'Email notifications disabled', 'sent' => false];
    }

    Mail::send(new ClaimNotificationMail($this, $type, $additionalData));

    return ['success' => true, 'message' => 'Email sent successfully', 'sent' => true];
}
```

**Notification Type Methods**:
```php
// Claim created notification
public function sendClaimCreatedNotification(): void
{
    $this->sendEmailNotification('claim_created');
}

// Stage update notification
public function sendStageUpdateNotification(string $stageName, ?string $description = null, ?string $notes = null): void
{
    $this->sendEmailNotification('stage_update', [
        'stage_name' => $stageName,
        'description' => $description,
        'notes' => $notes,
    ]);
}

// Claim number assigned notification
public function sendClaimNumberAssignedNotification(): void
{
    $this->sendEmailNotification('claim_number_assigned');
}

// Document request notification
public function sendDocumentRequestNotification(): void
{
    $pendingDocuments = $this->documents()
        ->where('is_submitted', false)
        ->get()
        ->map(fn($doc) => [
            'name' => $doc->document_name,
            'description' => $doc->description,
            'required' => $doc->is_required,
        ])
        ->toArray();

    $this->sendEmailNotification('document_request', [
        'pending_documents' => $pendingDocuments,
    ]);
}

// Claim closure notification
public function sendClaimClosureNotification(?string $reason = null): void
{
    $this->sendEmailNotification('claim_closed', [
        'closure_reason' => $reason,
    ]);
}
```

## ClaimStage Model

**File**: `app/Models/ClaimStage.php`

### Attributes

- `claim_id` (bigint) - Parent claim reference
- `stage_name` (string) - Stage name (e.g., "Documents Submitted", "Under Review", "Approved")
- `description` (text, nullable) - Stage description
- `is_current` (boolean) - Current active stage indicator
- `is_completed` (boolean) - Stage completion status
- `stage_date` (datetime, nullable) - Stage timestamp
- `notes` (text, nullable) - Additional notes

### Methods

**Set as Current Stage**:
```php
public function setAsCurrent(): void
{
    // Mark all other stages for this claim as not current
    self::query()->where('claim_id', $this->claim_id)
        ->where('id', '!=', $this->id)
        ->update(['is_current' => false]);

    // Mark this stage as current
    $this->update(['is_current' => true]);
}
```

## ClaimDocument Model

**File**: `app/Models/ClaimDocument.php`

### Attributes

- `claim_id` (bigint) - Parent claim reference
- `document_name` (string) - Document name (e.g., "FIR", "Policy Copy")
- `description` (text, nullable) - Document purpose description
- `is_required` (boolean) - Required vs optional document
- `is_submitted` (boolean) - Submission status
- `document_path` (string, nullable) - File storage path
- `submitted_date` (datetime, nullable) - Submission timestamp
- `notes` (text, nullable) - Document-specific notes

### Methods

**Mark as Submitted**:
```php
public function markAsSubmitted(?string $documentPath = null): void
{
    $this->update([
        'is_submitted' => true,
        'submitted_date' => now(),
        'document_path' => $documentPath,
    ]);
}
```

**Mark as Not Submitted**:
```php
public function markAsNotSubmitted(): void
{
    $this->update([
        'is_submitted' => false,
        'submitted_date' => null,
        'document_path' => null,
    ]);
}
```

**Status Badge & Text**:
```php
public function getStatusBadgeClass(): string
{
    if ($this->is_submitted) return 'badge-success';
    return $this->is_required ? 'badge-danger' : 'badge-warning';
}

public function getStatusText(): string
{
    if ($this->is_submitted) return 'Submitted';
    return $this->is_required ? 'Required' : 'Optional';
}
```

**File Helpers**:
```php
public function hasFile(): bool
{
    return !empty($this->document_path) && file_exists(storage_path('app/public/' . $this->document_path));
}

public function getDocumentUrl(): ?string
{
    return $this->document_path ? asset('storage/' . $this->document_path) : null;
}
```

## ClaimLiabilityDetail Model

**File**: `app/Models/ClaimLiabilityDetail.php`

### Attributes

**Claim Type**
- `claim_type` (string) - Cashless or Reimbursement

**Facility Details**
- `hospital_name` (string, nullable) - Hospital name for health claims
- `hospital_address` (text, nullable) - Hospital address
- `garage_name` (string, nullable) - Garage name for vehicle claims
- `garage_address` (text, nullable) - Garage address

**Amount Tracking**
- `estimated_amount` (decimal) - Initial repair/treatment estimate
- `approved_amount` (decimal) - Insurance approved amount
- `final_amount` (decimal) - Final settlement amount
- `claim_amount` (decimal) - Total claim amount
- `salvage_amount` (decimal) - Salvage value (vehicle claims)
- `less_claim_charge` (decimal) - Deductions - claim processing charges
- `less_salvage_amount` (decimal) - Deductions - salvage amount
- `less_deductions` (decimal) - Other deductions
- `amount_to_be_paid` (decimal) - Net payable after deductions
- `claim_amount_received` (decimal) - Actual amount received
- `notes` (text, nullable) - Liability notes

### Methods

**Claim Type Checks**:
```php
public function isCashless(): bool
{
    return $this->claim_type === 'Cashless';
}

public function isReimbursement(): bool
{
    return $this->claim_type === 'Reimbursement';
}
```

**Facility Helpers**:
```php
public function getFacilityName(): ?string
{
    return $this->hospital_name ?? $this->garage_name;
}

public function getFacilityAddress(): ?string
{
    return $this->hospital_address ?? $this->garage_address;
}
```

**Formatted Amounts**:
```php
public function getFormattedEstimatedAmount(): string
{
    return $this->estimated_amount ? '₹' . number_format($this->estimated_amount, 2) : '-';
}

public function getFormattedApprovedAmount(): string
{
    return $this->approved_amount ? '₹' . number_format($this->approved_amount, 2) : '-';
}

public function getFormattedFinalAmount(): string
{
    return $this->final_amount ? '₹' . number_format($this->final_amount, 2) : '-';
}
```

## ClaimService

**File**: `app/Services/ClaimService.php`

### Create Claim

```php
public function createClaim(StoreClaimRequest $request): Claim
{
    return $this->createInTransaction(function() use ($request) {
        // 1. Get customer insurance with customer relationship
        $customerInsurance = CustomerInsurance::with('customer')
            ->findOrFail($request->customer_insurance_id);

        // 2. Generate claim number
        $claimNumber = Claim::generateClaimNumber();

        // 3. Prepare claim data
        $claimData = $request->validated();
        $claimData['claim_number'] = $claimNumber;
        $claimData['customer_id'] = $customerInsurance->customer_id;

        // 4. Set WhatsApp number from customer if not provided
        if (empty($claimData['whatsapp_number'])) {
            $claimData['whatsapp_number'] = $customerInsurance->customer->mobile_number;
        }

        // 5. Create claim
        $claim = Claim::query()->create($claimData);

        // 6. Create default documents (10 for Health, 20 for Vehicle)
        $claim->createDefaultDocuments();

        // 7. Create initial stage ("Claim Registered")
        $claim->createInitialStage();

        // 8. Create liability detail with default claim type
        $claim->liabilityDetail()->create([
            'claim_type' => $claim->insurance_type === 'Health' ? 'Cashless' : 'Reimbursement',
            'notes' => 'Initial liability detail record created',
        ]);

        // 9. Send email notification (after transaction)
        $claim->sendClaimCreatedNotification();

        Log::info('Claim created successfully', [
            'claim_id' => $claim->id,
            'claim_number' => $claim->claim_number,
            'customer_id' => $claim->customer_id,
            'user_id' => auth()->id(),
        ]);

        return $claim;
    });
}
```

### Update Claim

```php
public function updateClaim(UpdateClaimRequest $request, Claim $claim): bool
{
    return $this->updateInTransaction(function() use ($request, $claim) {
        // 1. If insurance changed, update customer_id
        if ($request->customer_insurance_id !== $claim->customer_insurance_id) {
            $customerInsurance = CustomerInsurance::with('customer')
                ->findOrFail($request->customer_insurance_id);
            $updateData = $request->validated();
            $updateData['customer_id'] = $customerInsurance->customer_id;
        } else {
            $updateData = $request->validated();
        }

        // 2. Set WhatsApp from new customer if not provided
        if (empty($updateData['whatsapp_number']) && isset($customerInsurance)) {
            $updateData['whatsapp_number'] = $customerInsurance->customer->mobile_number;
        }

        // 3. Update claim
        $updated = $claim->update($updateData);

        // 4. Sync insurance_type to liability detail's claim_type
        if (isset($updateData['insurance_type']) && $claim->liabilityDetail) {
            $newClaimType = $updateData['insurance_type'] === 'Health' ? 'Cashless' : 'Reimbursement';
            $claim->liabilityDetail->update(['claim_type' => $newClaimType]);
        }

        return $updated;
    });
}
```

### Search Policies (AJAX Autocomplete)

```php
public function searchPolicies(string $searchTerm): array
{
    if (strlen($searchTerm) < 3) return [];

    $policies = CustomerInsurance::with([
        'customer:id,name,email,mobile_number',
        'insuranceCompany:id,name',
        'policyType:id,name',
    ])
    ->where(function(Builder $builder) use ($searchTerm) {
        $builder->where('policy_no', 'like', "%{$searchTerm}%")
            ->orWhere('registration_no', 'like', "%{$searchTerm}%")
            ->orWhereHas('customer', function(Builder $builder) use ($searchTerm) {
                $builder->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%")
                    ->orWhere('mobile_number', 'like', "%{$searchTerm}%");
            });
    })
    ->where('status', true) // Only active policies
    ->limit(20)
    ->get();

    return $policies->map(fn($policy) => [
        'id' => $policy->id,
        'text' => $this->formatPolicyText($policy),
        'customer_name' => $policy->customer->name ?? '',
        'customer_email' => $policy->customer->email ?? '',
        'customer_mobile' => $policy->customer->mobile_number ?? '',
        'policy_no' => $policy->policy_no ?? '',
        'registration_no' => $policy->registration_no ?? '',
        'insurance_company' => $policy->insuranceCompany->name ?? '',
        'policy_type' => $policy->policyType->name ?? '',
        'suggested_insurance_type' => $this->suggestInsuranceType($policy),
    ])->toArray();
}

private function suggestInsuranceType(CustomerInsurance $customerInsurance): string
{
    $policyTypeName = strtolower($customerInsurance->policyType->name ?? '');

    // Vehicle insurance indicators
    $vehicleKeywords = ['motor', 'vehicle', 'car', 'bike', 'auto', 'comprehensive', 'third party'];
    foreach ($vehicleKeywords as $keyword) {
        if (str_contains($policyTypeName, $keyword)) return 'Vehicle';
    }

    // Health insurance indicators
    $healthKeywords = ['health', 'medical', 'mediclaim', 'hospital', 'disease'];
    foreach ($healthKeywords as $keyword) {
        if (str_contains($policyTypeName, $keyword)) return 'Health';
    }

    // If registration number exists, likely vehicle
    if (!empty($customerInsurance->registration_no)) return 'Vehicle';

    // Default to Health if unclear
    return 'Health';
}
```

## ClaimController

**File**: `app/Http/Controllers/ClaimController.php`

### Key Routes

| Method | Route | Action | Description |
|--------|-------|--------|-------------|
| GET | `/claims` | `index()` | List all claims with filters |
| GET | `/claims/create` | `create()` | Show claim creation form |
| POST | `/claims` | `store()` | Create new claim |
| GET | `/claims/{id}` | `show()` | View claim details |
| GET | `/claims/{id}/edit` | `edit()` | Edit claim form |
| PUT | `/claims/{id}` | `update()` | Update claim |
| PUT | `/claims/{id}/status` | `updateStatus()` | Toggle active/inactive |
| DELETE | `/claims/{id}` | `delete()` | Soft delete claim |
| GET | `/claims/search-policies` | `searchPolicies()` | AJAX policy search |
| GET | `/claims/statistics` | `getStatistics()` | AJAX dashboard stats |
| POST | `/claims/{id}/send-document-list` | `sendDocumentListWhatsApp()` | Send initial document list |
| POST | `/claims/{id}/send-pending-documents` | `sendPendingDocumentsWhatsApp()` | Send pending reminder |
| POST | `/claims/{id}/send-claim-number` | `sendClaimNumberWhatsApp()` | Send claim number |
| GET | `/claims/{id}/whatsapp-preview/{type}` | `getWhatsAppPreview()` | Preview WhatsApp message |
| PUT | `/claims/{id}/documents/{docId}/status` | `updateDocumentStatus()` | Mark document submitted/pending |
| POST | `/claims/{id}/stages` | `addStage()` | Add new claim stage |
| PUT | `/claims/{id}/claim-number` | `updateClaimNumber()` | Update claim number |
| PUT | `/claims/{id}/liability-details` | `updateLiabilityDetails()` | Update liability amounts |

### AJAX Endpoints

**Update Document Status**:
```php
public function updateDocumentStatus(Request $request, Claim $claim, int $documentId): JsonResponse
{
    $document = $claim->documents()->findOrFail($documentId);
    $isSubmitted = $request->boolean('is_submitted');

    if ($isSubmitted) {
        $document->markAsSubmitted();
    } else {
        $document->markAsNotSubmitted();
    }

    return response()->json([
        'success' => true,
        'message' => 'Document status updated successfully',
        'document_completion' => $claim->getDocumentCompletionPercentage(),
        'required_completion' => $claim->getRequiredDocumentCompletionPercentage(),
    ]);
}
```

**Add Claim Stage**:
```php
public function addStage(Request $request, Claim $claim): JsonResponse
{
    $request->validate([
        'stage_name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'notes' => 'nullable|string',
        'send_whatsapp' => 'boolean',
    ]);

    // Mark current stage as not current
    $claim->stages()->where('is_current', true)->update(['is_current' => false]);

    // Create new stage
    $stage = $claim->stages()->create([
        'stage_name' => $request->stage_name,
        'description' => $request->description,
        'notes' => $request->notes,
        'is_current' => true,
        'is_completed' => false,
        'stage_date' => now(),
    ]);

    // Send WhatsApp if requested
    $whatsappResult = null;
    if ($request->boolean('send_whatsapp')) {
        $whatsappResult = $claim->sendStageUpdateWhatsApp($request->stage_name, $request->notes);
    }

    // Send email notification if enabled
    $claim->sendStageUpdateNotification($request->stage_name, $request->description, $request->notes);

    return response()->json([
        'success' => true,
        'message' => 'Stage added successfully',
        'stage' => $stage,
        'whatsapp_result' => $whatsappResult,
    ]);
}
```

**Update Liability Details**:
```php
public function updateLiabilityDetails(Request $request, Claim $claim): JsonResponse
{
    $request->validate([
        'claim_type' => 'required|in:Cashless,Reimbursement',
        'claim_amount' => 'nullable|numeric|min:0',
        'salvage_amount' => 'nullable|numeric|min:0',
        'less_claim_charge' => 'nullable|numeric|min:0',
        'amount_to_be_paid' => 'nullable|numeric|min:0',
        'less_salvage_amount' => 'nullable|numeric|min:0',
        'less_deductions' => 'nullable|numeric|min:0',
        'claim_amount_received' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string',
    ]);

    $liabilityDetail = $claim->liabilityDetail ?: $claim->liabilityDetail()->create([]);
    $liabilityDetail->update($request->all());

    return response()->json([
        'success' => true,
        'message' => 'Liability details updated successfully',
        'liability_detail' => $liabilityDetail->fresh(),
    ]);
}
```

## Database Schema

### claims Table

```sql
CREATE TABLE claims (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    claim_number VARCHAR(255) UNIQUE NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    customer_insurance_id BIGINT UNSIGNED NOT NULL,
    insurance_type VARCHAR(50) NOT NULL, -- Health, Vehicle
    incident_date DATE NOT NULL,
    description TEXT NULL,
    whatsapp_number VARCHAR(20),
    send_email_notifications BOOLEAN DEFAULT TRUE,
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    deleted_by BIGINT UNSIGNED NULL,

    INDEX idx_claim_number (claim_number),
    INDEX idx_customer (customer_id),
    INDEX idx_insurance (customer_insurance_id),
    INDEX idx_insurance_type (insurance_type),
    INDEX idx_status (status),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_insurance_id) REFERENCES customer_insurances(id) ON DELETE CASCADE
);
```

### claim_stages Table

```sql
CREATE TABLE claim_stages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    claim_id BIGINT UNSIGNED NOT NULL,
    stage_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_current BOOLEAN DEFAULT FALSE,
    is_completed BOOLEAN DEFAULT FALSE,
    stage_date TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    deleted_by BIGINT UNSIGNED NULL,

    INDEX idx_claim (claim_id),
    INDEX idx_current (is_current),
    INDEX idx_completed (is_completed),
    FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE
);
```

### claim_documents Table

```sql
CREATE TABLE claim_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    claim_id BIGINT UNSIGNED NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_required BOOLEAN DEFAULT TRUE,
    is_submitted BOOLEAN DEFAULT FALSE,
    document_path VARCHAR(500) NULL,
    submitted_date TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    deleted_by BIGINT UNSIGNED NULL,

    INDEX idx_claim (claim_id),
    INDEX idx_required (is_required),
    INDEX idx_submitted (is_submitted),
    FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE
);
```

### claim_liability_details Table

```sql
CREATE TABLE claim_liability_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    claim_id BIGINT UNSIGNED NOT NULL,
    claim_type VARCHAR(50) DEFAULT 'Reimbursement', -- Cashless, Reimbursement
    hospital_name VARCHAR(255) NULL,
    hospital_address TEXT NULL,
    garage_name VARCHAR(255) NULL,
    garage_address TEXT NULL,
    estimated_amount DECIMAL(10,2) NULL,
    approved_amount DECIMAL(10,2) NULL,
    final_amount DECIMAL(10,2) NULL,
    claim_amount DECIMAL(10,2) NULL,
    salvage_amount DECIMAL(10,2) NULL,
    less_claim_charge DECIMAL(10,2) NULL,
    amount_to_be_paid DECIMAL(10,2) NULL,
    less_salvage_amount DECIMAL(10,2) NULL,
    less_deductions DECIMAL(10,2) NULL,
    claim_amount_received DECIMAL(10,2) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    deleted_by BIGINT UNSIGNED NULL,

    INDEX idx_claim (claim_id),
    INDEX idx_claim_type (claim_type),
    FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE
);
```

## Usage Examples

### Example 1: Create Health Insurance Claim

```php
use App\Services\ClaimService;

$claimService = app(ClaimService::class);

// Create health insurance claim
$request = new StoreClaimRequest([
    'customer_insurance_id' => 45, // Health insurance policy
    'insurance_type' => 'Health',
    'incident_date' => '2025-11-05', // Admission date
    'description' => 'Patient admitted for heart surgery',
    'whatsapp_number' => '9876543210',
    'send_email_notifications' => true,
    'status' => true,
]);

$claim = $claimService->createClaim($request);

echo "Claim Number: {$claim->claim_number}\n"; // CLM202511 0001
echo "Documents Created: {$claim->documents()->count()}\n"; // 10 (Health)
echo "Initial Stage: {$claim->currentStage->stage_name}\n"; // "Claim Registered"
echo "Liability Type: {$claim->liabilityDetail->claim_type}\n"; // "Cashless"

// Send initial document list via WhatsApp
$result = $claim->sendDocumentListWhatsApp();
echo $result['message']; // "Document list sent via WhatsApp successfully"
```

### Example 2: Create Vehicle Insurance Claim

```php
$request = new StoreClaimRequest([
    'customer_insurance_id' => 78, // Vehicle insurance policy
    'insurance_type' => 'Vehicle',
    'incident_date' => '2025-11-03', // Accident date
    'description' => 'Front bumper and headlight damaged in collision',
    'whatsapp_number' => '9123456789',
    'send_email_notifications' => false, // Disabled for this claim
    'status' => true,
]);

$claim = $claimService->createClaim($request);

echo "Claim Number: {$claim->claim_number}\n"; // CLM202511 0002
echo "Documents Created: {$claim->documents()->count()}\n"; // 20 (Vehicle)
echo "Liability Type: {$claim->liabilityDetail->claim_type}\n"; // "Reimbursement"

// Send claim number notification
$claim->sendClaimNumberWhatsApp();
// Message: "Dear customer your Claim Number CLM2025110002 is generated against your vehicle number MH02AB1234..."
```

### Example 3: Track Document Submission

```php
$claim = Claim::with('documents')->find(1);

// Initially all documents pending
echo "Overall Completion: {$claim->getDocumentCompletionPercentage()}%\n"; // 0%
echo "Required Completion: {$claim->getRequiredDocumentCompletionPercentage()}%\n"; // 0%

// Mark documents as submitted
$policyDoc = $claim->documents()->where('document_name', 'Policy Copy')->first();
$policyDoc->markAsSubmitted('uploads/claims/policy_12345.pdf');

$rcDoc = $claim->documents()->where('document_name', 'RC Copy')->first();
$rcDoc->markAsSubmitted('uploads/claims/rc_12345.pdf');

$licenseDoc = $claim->documents()->where('document_name', 'Driving License')->first();
$licenseDoc->markAsSubmitted();

// Refresh claim
$claim->refresh();

echo "Overall Completion: {$claim->getDocumentCompletionPercentage()}%\n"; // 15% (3/20)
echo "Required Completion: {$claim->getRequiredDocumentCompletionPercentage()}%\n"; // 18.75% (3/16)

// Send pending documents reminder
$claim->sendPendingDocumentsWhatsApp();
// Message: "Below are the Documents pending from your side...
// 1. Claim form duly Signed
// 2. Driver Contact Number
// 3. Spot Location Address
// ..."
```

### Example 4: Manage Claim Stages

```php
$claim = Claim::find(1);

// Initial stage
echo "Current Stage: {$claim->currentStage->stage_name}\n"; // "Claim Registered"

// Add "Documents Submitted" stage
$claim->stages()->where('is_current', true)->update(['is_current' => false]);

$stage2 = $claim->stages()->create([
    'stage_name' => 'Documents Submitted',
    'description' => 'All required documents received and verified',
    'is_current' => true,
    'is_completed' => false,
    'stage_date' => now(),
    'notes' => 'Received all 16 required documents',
]);

// Send WhatsApp notification
$claim->sendStageUpdateWhatsApp('Documents Submitted', 'Received all 16 required documents');

// Add "Under Review" stage
$stage2->update(['is_current' => false, 'is_completed' => true]);

$stage3 = $claim->stages()->create([
    'stage_name' => 'Under Review',
    'description' => 'Claim is being reviewed by insurance company',
    'is_current' => true,
    'is_completed' => false,
    'stage_date' => now(),
    'notes' => 'Submitted to ICICI Lombard for review',
]);

// Send email notification (if enabled)
$claim->sendStageUpdateNotification('Under Review', 'Claim is being reviewed by insurance company', 'Expected review time: 7-10 days');

// View stage history
$stages = $claim->stages()->orderBy('created_at')->get();
foreach ($stages as $stage) {
    $current = $stage->is_current ? ' (CURRENT)' : '';
    $completed = $stage->is_completed ? ' ✓' : '';
    echo "{$stage->stage_date_formatted}: {$stage->stage_name}{$current}{$completed}\n";
}

// Output:
// 05/11/2025: Claim Registered ✓
// 06/11/2025: Documents Submitted ✓
// 06/11/2025: Under Review (CURRENT)
```

### Example 5: Update Liability Details

```php
$claim = Claim::with('liabilityDetail')->find(1);

// Update garage and amounts for vehicle claim
$claim->liabilityDetail->update([
    'garage_name' => 'Maruti Authorized Service Center',
    'garage_address' => '123 Main Road, Mumbai, MH - 400001',
    'estimated_amount' => 45000.00, // Workshop estimate
    'approved_amount' => 42000.00,  // Insurance approved amount
    'salvage_amount' => 3000.00,    // Salvage value of damaged parts
    'less_claim_charge' => 500.00,  // Processing charges
    'less_salvage_amount' => 3000.00, // Deduct salvage
    'less_deductions' => 1500.00,   // Other deductions (depreciation)
    'claim_amount' => 42000.00,
    'amount_to_be_paid' => 37000.00, // 42000 - 500 - 3000 - 1500
    'notes' => 'Front bumper and headlight replacement approved',
]);

echo "Facility: {$claim->liabilityDetail->getFacilityName()}\n"; // "Maruti Authorized Service Center"
echo "Estimated: {$claim->liabilityDetail->getFormattedEstimatedAmount()}\n"; // "₹45,000.00"
echo "Approved: {$claim->liabilityDetail->getFormattedApprovedAmount()}\n"; // "₹42,000.00"
echo "Final Payable: {$claim->liabilityDetail->getFormattedFinalAmount()}\n"; // "₹37,000.00"
```

### Example 6: Search Policies (AJAX)

```php
// In claim creation form, customer types "MH02AB"
$searchTerm = "MH02AB";
$policies = $claimService->searchPolicies($searchTerm);

foreach ($policies as $policy) {
    echo "Policy #{$policy['id']}: {$policy['text']}\n";
    echo "  Customer: {$policy['customer_name']} ({$policy['customer_mobile']})\n";
    echo "  Policy: {$policy['policy_no']}\n";
    echo "  Registration: {$policy['registration_no']}\n";
    echo "  Company: {$policy['insurance_company']}\n";
    echo "  Suggested Type: {$policy['suggested_insurance_type']}\n\n";
}

// Output:
// Policy #78: Rajesh Kumar - Policy: ICICI/2024/M/12345 - Reg: MH02AB1234 - ICICI Lombard
//   Customer: Rajesh Kumar (9876543210)
//   Policy: ICICI/2024/M/12345
//   Registration: MH02AB1234
//   Company: ICICI Lombard
//   Suggested Type: Vehicle
```

### Example 7: Claim Statistics (Dashboard)

```php
$statistics = $claimService->getClaimStatistics();

echo "Total Claims: {$statistics['total_claims']}\n";
echo "Active Claims: {$statistics['active_claims']}\n";
echo "Pending Documentation: {$statistics['pending_documentation']}\n";
echo "Under Review: {$statistics['under_review']}\n";
echo "Approved Claims: {$statistics['approved_claims']}\n";
echo "Settled Claims: {$statistics['settled_claims']}\n";
echo "Settlement Rate: {$statistics['settlement_rate']}%\n";

echo "\nBy Insurance Type:\n";
echo "Health Claims: {$statistics['health_claims']}\n";
echo "Vehicle Claims: {$statistics['vehicle_claims']}\n";

// Output:
// Total Claims: 142
// Active Claims: 38
// Pending Documentation: 12
// Under Review: 18
// Approved Claims: 8
// Settled Claims: 104
// Settlement Rate: 73.24%
//
// By Insurance Type:
// Health Claims: 56
// Vehicle Claims: 86
```

### Example 8: Complete Claim Workflow

```php
// 1. Create claim
$claim = $claimService->createClaim($request);
echo "Created: {$claim->claim_number}\n";

// 2. Send initial document list
$claim->sendDocumentListWhatsApp();
$claim->sendDocumentRequestNotification(); // Email if enabled

// 3. Customer submits documents (over time)
$docs = $claim->documents()->where('is_required', true)->get();
foreach ($docs as $doc) {
    $doc->markAsSubmitted("uploads/claims/{$claim->id}/{$doc->document_name}.pdf");
}

echo "Required Completion: {$claim->getRequiredDocumentCompletionPercentage()}%\n"; // 100%

// 4. Update stage to "Documents Verified"
$claim->stages()->where('is_current', true)->update(['is_current' => false, 'is_completed' => true]);
$claim->stages()->create([
    'stage_name' => 'Documents Verified',
    'description' => 'All documents verified and submitted to insurance company',
    'is_current' => true,
    'stage_date' => now(),
]);
$claim->sendStageUpdateWhatsApp('Documents Verified');

// 5. Insurance company reviews (external process)
sleep(7 * 24 * 3600); // 7 days later...

// 6. Update liability details with approved amounts
$claim->liabilityDetail->update([
    'garage_name' => 'ABC Motors',
    'garage_address' => '789 Service Rd, Pune',
    'estimated_amount' => 50000,
    'approved_amount' => 48000,
    'final_amount' => 45000,
]);

// 7. Add "Approved" stage
$claim->stages()->create([
    'stage_name' => 'Approved',
    'description' => 'Claim approved for settlement',
    'is_current' => true,
    'stage_date' => now(),
    'notes' => 'Approved amount: ₹45,000',
]);
$claim->sendStageUpdateWhatsApp('Approved', 'Approved amount: ₹45,000');

// 8. Add "Settled" stage
$claim->liabilityDetail->update(['claim_amount_received' => 45000]);
$claim->stages()->create([
    'stage_name' => 'Settled',
    'description' => 'Claim amount paid to customer',
    'is_current' => true,
    'is_completed' => true,
    'stage_date' => now(),
]);
$claim->sendClaimClosureNotification('Claim settled successfully. Amount credited to registered account.');

echo "Claim settled: ₹45,000 paid to customer\n";
```

## Related Documentation

- **[POLICY_MANAGEMENT.md](POLICY_MANAGEMENT.md)** - Insurance policies linked to claims
- **[CUSTOMER_MANAGEMENT.md](CUSTOMER_MANAGEMENT.md)** - Customer data and relationships
- **[NOTIFICATION_SYSTEM.md](../features/NOTIFICATION_SYSTEM.md)** - WhatsApp/Email notification infrastructure
- **[APP_SETTINGS.md](../features/APP_SETTINGS.md)** - Email notification global settings

## Notification Templates

### WhatsApp Message Templates

**Health Insurance Document List**:
```
For health Insurance - Kindly provide below mention details for Claim intimation

1. Patient Name
2. Policy No
3. Contact no
4. Date of Admission
5. Treating Doctor Name
6. Hospital Name
7. Address of Hospital
8. Illness
9. Approx Hospitalisation Days
10. Approx Cost

Best regards,
{Advisor Name}
{Website}
{Company Title}
"{Tagline}"
```

**Vehicle Insurance Document List**:
```
For Vehicle/Truck Insurance - Kindly provide below mention details for Claim intimation

1. Claim form duly Signed
2. Policy Copy
3. RC Copy
4. Driving License
5. Driver Contact Number
... (20 items total)

Best regards,
{Company Details}
```

**Pending Documents Reminder**:
```
Below are the Documents pending from your side, Send it urgently for hassle free claim service

1. FIR - Yes or No
2. Workshop Estimate
3. All side spot Photos with driver selfie

Best regards,
{Company Details}
```

**Claim Number Notification**:
```
Dear customer your Claim Number CLM202511 0001 is generated against your vehicle number MH02AB1234. For further assistance kindly contact me.

Best regards,
{Company Details}
```

**Stage Update**:
```
Dear {Customer Name},

Your claim CLM2025110001 status has been updated to: *Approved*

Notes: Approved amount: ₹45,000

For further assistance, please contact us.

Best regards,
{Company Details}
```

---

**Last Updated**: 2025-11-06
**Document Version**: 1.0
