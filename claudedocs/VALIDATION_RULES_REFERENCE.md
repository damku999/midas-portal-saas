# Validation Rules Reference
**Technical Documentation - Laravel Validation Rules**

Complete reference of all validation rules extracted from controllers, services, and Form Request classes.

---

## Table of Contents

1. [Validation Rules by Module](#validation-rules-by-module)
2. [Laravel Validation Syntax](#laravel-validation-syntax)
3. [Custom Validation Rules](#custom-validation-rules)
4. [Validation Rule Patterns](#validation-rule-patterns)

---

## Validation Rules by Module

### 1. Branches

**Controller**: `BranchController.php`

#### Store Validation (POST)
```php
[
    'name' => 'required|string|max:255|unique:branches,name',
]
```

#### Update Validation (PUT)
```php
[
    'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
]
```

#### Fillable Fields
```php
protected $fillable = [
    'name',
    'email',
    'mobile_number',
    'status',
];
```

**Note**: Only `name` is validated in controller. Other fields are accepted without explicit validation.

---

### 2. Brokers

**Controller**: `BrokerController.php`
**Form Request**: `StoreBrokerRequest.php`, `UpdateBrokerRequest.php`

#### Store/Update Validation
```php
[
    'name' => 'required|string|max:255',
    'email' => 'nullable|email|max:255',
    'mobile_number' => 'nullable|numeric|digits:10',
]
```

#### Custom Error Messages
```php
[
    'name.required' => 'Broker name is required.',
    'name.string' => 'Broker name must be a valid string.',
    'name.max' => 'Broker name cannot exceed 255 characters.',
    'email.email' => 'Please provide a valid email address.',
    'email.max' => 'Email cannot exceed 255 characters.',
    'mobile_number.numeric' => 'Mobile number must be numeric.',
    'mobile_number.digits' => 'Mobile number must be exactly 10 digits.',
]
```

#### Fillable Fields
```php
protected $fillable = [
    'name',
    'email',
    'mobile_number',
    'status',
];
```

---

### 3. Insurance Companies

**Controller**: `InsuranceCompanyController.php`

#### Store/Update Validation
```php
[
    'name' => 'required',
]
```

**Note**: Minimal validation. Only name is validated.

#### Fillable Fields
```php
protected $fillable = [
    'name',
    'email',
    'mobile_number',
    'status',
];
```

---

### 4. Customers

**Controller**: `CustomerController.php`
**Form Request**: `StoreCustomerRequest.php`, `UpdateCustomerRequest.php`

#### Store Validation (POST)
```php
[
    'name' => 'required|string|max:255',
    'email' => 'required|email|max:255|unique:customers,email',
    'mobile_number' => 'required|numeric|digits:10',
    'status' => 'required|numeric|in:0,1',
    'type' => 'required|in:Retail,Corporate',
    'pan_card_number' => 'required_if:type,Retail|nullable|string|max:10',
    'pan_card_path' => 'nullable|file|max:1024|mimetypes:application/pdf,image/jpeg,image/png',
    'aadhar_card_number' => 'required_if:type,Retail|nullable|string|max:12',
    'aadhar_card_path' => 'nullable|file|max:1024|mimetypes:application/pdf,image/jpeg,image/png',
    'gst_number' => 'required_if:type,Corporate|nullable|string|max:15',
    'gst_path' => 'nullable|file|max:1024|mimetypes:application/pdf,image/jpeg,image/png',
]

// Dynamic date field validation (if provided)
if (!empty($this->date_of_birth)) {
    $rules['date_of_birth'] = 'date';
}
if (!empty($this->wedding_anniversary_date)) {
    $rules['wedding_anniversary_date'] = 'date';
}
if (!empty($this->engagement_anniversary_date)) {
    $rules['engagement_anniversary_date'] = 'date';
}
```

#### Update Validation (PUT)
Same as store, except:
```php
'email' => 'required|email|max:255|unique:customers,email,' . $customer->id,
```

#### Custom Error Messages
```php
[
    'name.required' => 'Customer name is required.',
    'email.required' => 'Email address is required.',
    'email.email' => 'Please provide a valid email address.',
    'email.unique' => 'This email address is already taken.',
    'mobile_number.required' => 'Mobile number is required.',
    'mobile_number.digits' => 'Mobile number must be exactly 10 digits.',
    'type.required' => 'Customer type is required.',
    'type.in' => 'Customer type must be either Retail or Corporate.',
    'pan_card_number.required_if' => 'PAN card number is required for Retail customers.',
    'aadhar_card_number.required_if' => 'Aadhar card number is required for Retail customers.',
    'gst_number.required_if' => 'GST number is required for Corporate customers.',
    'date_of_birth.date' => 'Date of birth must be a valid date.',
    'wedding_anniversary_date.date' => 'Wedding anniversary date must be a valid date.',
    'engagement_anniversary_date.date' => 'Engagement anniversary date must be a valid date.',
]
```

#### Date Format Transformation
```php
// Converts DD/MM/YYYY to YYYY-MM-DD before validation
protected function prepareForValidation()
{
    $dateFields = ['date_of_birth', 'wedding_anniversary_date', 'engagement_anniversary_date'];
    foreach ($dateFields as $dateField) {
        if ($this->has($dateField) && $this->$dateField) {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $this->$dateField)) {
                $dateParts = explode('/', $this->$dateField);
                $data[$dateField] = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
            }
        }
    }
    $this->merge($data);
}
```

#### Fillable Fields
```php
protected $fillable = [
    'name',
    'email',
    'mobile_number',
    'status',
    'wedding_anniversary_date',
    'date_of_birth',
    'engagement_anniversary_date',
    'pan_card_number',
    'aadhar_card_number',
    'gst_number',
    'pan_card_path',
    'aadhar_card_path',
    'gst_path',
    'type',
    'family_group_id',
    'password',
    'email_verified_at',
    'password_changed_at',
    'must_change_password',
    'email_verification_token',
    'password_reset_sent_at',
    'password_reset_token',
    'password_reset_expires_at'
];
```

---

### 5. Customer Insurances (Policies)

**Controller**: `CustomerInsuranceController.php`
**Service**: `CustomerInsuranceService.php`

#### Store Validation (POST)
```php
// From CustomerInsuranceService::getStoreValidationRules()
[
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
]
```

#### Update Validation (PUT)
```php
// From CustomerInsuranceService::getUpdateValidationRules()
// Returns array of field names only (no rules), implying flexible update
[
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
    // ... (all 48+ fields)
]
```

#### Renewal Validation
```php
// Same as Store validation
public function getRenewalValidationRules(): array
{
    return $this->getStoreValidationRules();
}
```

#### Status Update Validation
```php
[
    'customer_insurance_id' => 'required|exists:customer_insurances,id',
    'status' => 'required|in:0,1',
]
```

#### Fillable Fields
```php
protected $fillable = [
    'issue_date',
    'branch_id',
    'broker_id',
    'relationship_manager_id',
    'customer_id',
    'insurance_company_id',
    'premium_type_id',
    'policy_type_id',
    'fuel_type_id',
    'policy_no',
    'registration_no',
    'rto',
    'make_model',
    'commission_on',
    'start_date',
    'expired_date',
    'tp_expiry_date',
    'maturity_date',
    'od_premium',
    'tp_premium',
    'net_premium',
    'premium_amount',
    'gst',
    'final_premium_with_gst',
    'sgst1',
    'cgst1',
    'cgst2',
    'sgst2',
    'my_commission_percentage',
    'my_commission_amount',
    'transfer_commission_percentage',
    'transfer_commission_amount',
    'reference_commission_percentage',
    'reference_commission_amount',
    'actual_earnings',
    'ncb_percentage',
    'mode_of_payment',
    'cheque_no',
    'insurance_status',
    'policy_document_path',
    'gross_vehicle_weight',
    'mfg_year',
    'reference_by',
    'plan_name',
    'premium_paying_term',
    'policy_term',
    'sum_insured',
    'pension_amount_yearly',
    'approx_maturity_amount',
    'life_insurance_payment_mode',
    'remarks',
    'status',
];
```

---

### 6. Claims

**Controller**: `ClaimController.php`
**Form Request**: `StoreClaimRequest.php`, `UpdateClaimRequest.php`

#### Store Validation (POST)
```php
[
    'customer_insurance_id' => 'required|exists:customer_insurances,id',
    'insurance_type' => 'required|in:Health,Vehicle',
    'incident_date' => 'required|date|before_or_equal:today',
    'description' => 'nullable|string|max:1000',
    'whatsapp_number' => 'nullable|string|regex:/^[+]?[0-9\s\-\(\)]+$/|max:15',
    'send_email_notifications' => 'boolean',
    'status' => 'required|boolean',
]
```

#### Custom Error Messages
```php
[
    'customer_insurance_id.required' => 'Please select a policy/insurance.',
    'customer_insurance_id.exists' => 'Selected policy/insurance is invalid.',
    'insurance_type.required' => 'Insurance type is required.',
    'insurance_type.in' => 'Insurance type must be either Health or Vehicle.',
    'incident_date.required' => 'Incident date is required.',
    'incident_date.date' => 'Incident date must be a valid date.',
    'incident_date.before_or_equal' => 'Incident date cannot be in the future.',
    'description.max' => 'Description cannot exceed 1000 characters.',
    'whatsapp_number.regex' => 'Please enter a valid WhatsApp number.',
    'whatsapp_number.max' => 'WhatsApp number cannot exceed 15 characters.',
    'status.required' => 'Status is required.',
    'status.boolean' => 'Status must be active or inactive.',
]
```

#### Custom Attributes
```php
[
    'customer_insurance_id' => 'policy/insurance',
    'insurance_type' => 'insurance type',
    'incident_date' => 'incident date',
    'whatsapp_number' => 'WhatsApp number',
    'send_email_notifications' => 'email notifications',
]
```

#### Date Format Transformation
```php
// Converts DD/MM/YYYY to YYYY-MM-DD
protected function prepareForValidation()
{
    if ($this->has('incident_date') && $this->incident_date) {
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $this->incident_date)) {
            $dateParts = explode('/', $this->incident_date);
            $this->merge([
                'incident_date' => $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0]
            ]);
        }
    }
}
```

#### Add Stage Validation (AJAX)
```php
[
    'stage_name' => 'required|string|max:255',
    'description' => 'nullable|string',
    'notes' => 'nullable|string',
    'send_whatsapp' => 'boolean',
]
```

#### Update Claim Number Validation (AJAX)
```php
[
    'claim_number' => 'required|string|max:255',
    'send_whatsapp' => 'boolean',
]
```

#### Update Liability Details Validation (AJAX)
```php
[
    'claim_type' => 'required|in:Cashless,Reimbursement',
    'claim_amount' => 'nullable|numeric|min:0',
    'salvage_amount' => 'nullable|numeric|min:0',
    'less_claim_charge' => 'nullable|numeric|min:0',
    'amount_to_be_paid' => 'nullable|numeric|min:0',
    'less_salvage_amount' => 'nullable|numeric|min:0',
    'less_deductions' => 'nullable|numeric|min:0',
    'claim_amount_received' => 'nullable|numeric|min:0',
    'notes' => 'nullable|string',
]
```

---

### 7. Quotations

**Controller**: `QuotationController.php`
**Form Request**: `CreateQuotationRequest.php`, `UpdateQuotationRequest.php`

#### Store Validation (POST)
```php
[
    'customer_id' => 'required|exists:customers,id',
    'vehicle_number' => 'nullable|string|max:20',
    'make_model_variant' => 'required|string|max:255',
    'rto_location' => 'required|string|max:255',
    'manufacturing_year' => 'required|integer|min:1980|max:' . (date('Y') + 1),
    'cubic_capacity_kw' => 'required|integer|min:1',
    'seating_capacity' => 'required|integer|min:1|max:50',
    'fuel_type' => 'required|in:Petrol,Diesel,CNG,Electric,Hybrid',
    'ncb_percentage' => 'nullable|numeric|min:0|max:50',
    'whatsapp_number' => 'nullable|string|regex:/^[6-9]\d{9}$/',
    'notes' => 'nullable|string|max:1000',
    // Quotation-level fields (optional)
    'policy_type' => 'nullable|in:Comprehensive,Own Damage,Third Party',
    'policy_tenure_years' => 'nullable|integer|in:1,2,3',
    'idv_vehicle' => 'nullable|numeric|min:10000|max:10000000',
    'idv_trailer' => 'nullable|numeric|min:0',
    'idv_cng_lpg_kit' => 'nullable|numeric|min:0',
    'idv_electrical_accessories' => 'nullable|numeric|min:0',
    'idv_non_electrical_accessories' => 'nullable|numeric|min:0',
    'total_idv' => 'nullable|numeric|min:0',
    // Companies array validation
    'companies' => 'nullable|array',
    'companies.*.insurance_company_id' => 'required_with:companies|exists:insurance_companies,id',
    'companies.*.quote_number' => 'nullable|string|max:255',
    // Coverage fields at company level
    'companies.*.policy_type' => 'required_with:companies|in:Comprehensive,Own Damage,Third Party',
    'companies.*.policy_tenure_years' => 'required_with:companies|integer|in:1,2,3',
    'companies.*.idv_vehicle' => 'required_with:companies|numeric|min:10000|max:10000000',
    'companies.*.idv_trailer' => 'nullable|numeric|min:0',
    'companies.*.idv_cng_lpg_kit' => 'nullable|numeric|min:0',
    'companies.*.idv_electrical_accessories' => 'nullable|numeric|min:0',
    'companies.*.idv_non_electrical_accessories' => 'nullable|numeric|min:0',
    'companies.*.total_idv' => 'nullable|numeric|min:0',
    // Premium fields
    'companies.*.basic_od_premium' => 'required_with:companies|numeric|min:0',
    'companies.*.tp_premium' => 'required_with:companies|numeric|min:0',
    'companies.*.total_addon_premium' => 'nullable|numeric|min:0',
    'companies.*.cng_lpg_premium' => 'nullable|numeric|min:0',
    'companies.*.net_premium' => 'nullable|numeric|min:0',
    'companies.*.sgst_amount' => 'nullable|numeric|min:0',
    'companies.*.cgst_amount' => 'nullable|numeric|min:0',
    'companies.*.total_premium' => 'nullable|numeric|min:0',
    'companies.*.final_premium' => 'nullable|numeric|min:0',
    'companies.*.total_od_premium' => 'nullable|numeric|min:0',
    'companies.*.is_recommended' => 'nullable|boolean',
    'companies.*.recommendation_note' => 'nullable|string|max:500',
]

// Dynamic addon field validations
$addonCovers = \App\Models\AddonCover::getOrdered(1);
foreach ($addonCovers as $addonCover) {
    $slug = \Str::slug($addonCover->name, '_');
    $rules["companies.*.addon_{$slug}"] = 'nullable|numeric|min:0';
    $rules["companies.*.addon_{$slug}_note"] = 'nullable|string|max:100';
    $rules["companies.*.addon_{$slug}_selected"] = 'nullable|string|in:0,1';
}

$rules['companies.*.addon_covers_breakdown'] = 'nullable|array';
```

#### Custom Error Messages
```php
[
    'customer_id.required' => 'Please select a customer.',
    'customer_id.exists' => 'Selected customer does not exist.',
    'make_model_variant.required' => 'Vehicle make, model and variant is required.',
    'rto_location.required' => 'RTO location is required.',
    'manufacturing_year.required' => 'Manufacturing year is required.',
    'manufacturing_year.min' => 'Manufacturing year must be 1980 or later.',
    'manufacturing_year.max' => 'Manufacturing year cannot be in the future.',
    'cubic_capacity_kw.required' => 'Engine capacity is required.',
    'seating_capacity.required' => 'Seating capacity is required.',
    'fuel_type.required' => 'Fuel type is required.',
    'fuel_type.in' => 'Please select a valid fuel type.',
    'ncb_percentage.numeric' => 'NCB percentage must be a valid number.',
    'ncb_percentage.min' => 'NCB percentage cannot be negative.',
    'ncb_percentage.max' => 'NCB percentage cannot exceed 50%.',
    'companies.*.policy_type.required_with' => 'Policy type is required for each company quote.',
    'companies.*.idv_vehicle.required_with' => 'Vehicle IDV is required for each company quote.',
    'companies.*.idv_vehicle.min' => 'Vehicle IDV must be at least ₹10,000.',
    'whatsapp_number.regex' => 'Please enter a valid 10-digit mobile number.',
]
```

---

### 8. Users (Admins)

**Controller**: `UserController.php`

#### Store Validation (POST)
```php
// From UserService::getStoreValidationRules()
[
    'first_name' => 'required|string|max:255',
    'last_name' => 'nullable|string|max:255',
    'email' => 'required|email|max:255|unique:users,email',
    'mobile_number' => 'nullable|numeric|digits:10',
    'role_id' => 'required|exists:roles,id',
    'new_password' => 'required|string|min:8|max:16|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
    'status' => 'required|in:0,1',
]
```

#### Update Validation (PUT)
```php
// From UserService::getUpdateValidationRules($user)
[
    'first_name' => 'required|string|max:255',
    'last_name' => 'nullable|string|max:255',
    'email' => 'required|email|max:255|unique:users,email,' . $user->id,
    'mobile_number' => 'nullable|numeric|digits:10',
    'role_id' => 'required|exists:roles,id',
    'status' => 'required|in:0,1',
]

// Password validation (optional, only if new_password provided)
// From UserService::getPasswordValidationRules()
[
    'new_password' => 'required|string|min:8|max:16|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
]
```

#### Custom Error Messages
```php
[
    'new_password.regex' => 'The new password format is invalid. It must contain at least one number, one special character, one uppercase letter, one lowercase letter, and be between 8 and 16 characters long.',
]
```

#### Status Update Validation
```php
[
    'user_id' => 'required|exists:users,id',
    'status' => 'required|in:0,1',
]
```

---

## Laravel Validation Syntax

### Basic Rules

#### Required
```php
'field' => 'required'              // Field must be present and not empty
'field' => 'nullable'               // Field can be null/empty
'field' => 'required_if:other,value' // Required if other field equals value
'field' => 'required_with:other'    // Required when other field is present
```

#### String Validations
```php
'field' => 'string'                 // Must be a string
'field' => 'string|max:255'         // String with max length
'field' => 'string|min:8'           // String with min length
'field' => 'string|between:8,16'    // String between lengths
```

#### Numeric Validations
```php
'field' => 'numeric'                // Must be numeric
'field' => 'integer'                // Must be an integer
'field' => 'digits:10'              // Must be exactly 10 digits
'field' => 'numeric|min:0'          // Minimum value
'field' => 'numeric|max:100'        // Maximum value
'field' => 'numeric|between:0,100'  // Between range
```

#### Email Validation
```php
'field' => 'email'                  // Valid email format
'field' => 'email|max:255'          // Email with max length
```

#### Date Validations
```php
'field' => 'date'                   // Valid date
'field' => 'date_format:d/m/Y'      // Specific format (DD/MM/YYYY)
'field' => 'before:today'           // Must be before today
'field' => 'after:start_date'       // Must be after start_date
'field' => 'before_or_equal:today'  // Cannot be in future
```

#### Unique Validation
```php
'field' => 'unique:table,column'              // Must be unique in table
'field' => 'unique:table,column,except_id'    // Unique except current record
'field' => 'unique:customers,email'           // Unique email in customers
'field' => 'unique:customers,email,' . $id    // Unique except this ID
```

#### Exists Validation
```php
'field' => 'exists:table,column'    // Must exist in table
'field' => 'exists:customers,id'    // Customer ID must exist
```

#### In Validation
```php
'field' => 'in:value1,value2'       // Must be one of the values
'field' => 'in:0,1'                 // Must be 0 or 1
'field' => 'in:Health,Vehicle'      // Must be Health or Vehicle
```

#### File Validation
```php
'field' => 'file'                   // Must be a file
'field' => 'file|max:1024'          // Max 1MB
'field' => 'mimetypes:application/pdf,image/jpeg,image/png'  // Allowed types
```

#### Boolean Validation
```php
'field' => 'boolean'                // Must be true/false, 1/0, "1"/"0"
```

#### Array Validation
```php
'field' => 'array'                  // Must be an array
'field' => 'array|min:1'            // Array with at least 1 item
'field.*' => 'required|string'      // Each array element must be string
'companies.*.name' => 'required'    // Nested array validation
```

#### Regex Validation
```php
'field' => 'regex:/^[6-9]\d{9}$/'   // Indian mobile number
'field' => 'regex:/^[+]?[0-9\s\-\(\)]+$/'  // WhatsApp number
```

---

## Custom Validation Rules

### Password Strength
```php
// Pattern: 8-16 chars, uppercase, lowercase, number, special char
'password' => 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
```

### Indian Mobile Number
```php
// Pattern: Starts with 6-9, exactly 10 digits
'mobile_number' => 'regex:/^[6-9]\d{9}$/'
// OR
'mobile_number' => 'numeric|digits:10'
```

### WhatsApp Number (International Format)
```php
// Pattern: Allows +, spaces, hyphens, parentheses
'whatsapp_number' => 'regex:/^[+]?[0-9\s\-\(\)]+$/'
```

### Date Format DD/MM/YYYY
```php
'date_field' => 'date_format:d/m/Y'
```

### Commission Calculation Base
```php
'commission_on' => 'in:net_premium,od_premium,tp_premium'
```

### Insurance Type
```php
'insurance_type' => 'in:Health,Vehicle'
```

### Customer Type
```php
'type' => 'in:Retail,Corporate'
```

### Policy Type
```php
'policy_type' => 'in:Comprehensive,Own Damage,Third Party'
```

### Fuel Type
```php
'fuel_type' => 'in:Petrol,Diesel,CNG,Electric,Hybrid'
```

### Claim Type
```php
'claim_type' => 'in:Cashless,Reimbursement'
```

### Notification Channel
```php
'channel' => 'in:email,whatsapp,both'
```

---

## Validation Rule Patterns

### Pattern 1: Simple Master Data
```php
// Branches, Fuel Types, Policy Types, Premium Types
[
    'name' => 'required|string|max:255',
    'status' => 'required|in:0,1',
]
```

### Pattern 2: Master Data with Contact Info
```php
// Brokers, Insurance Companies, Relationship Managers
[
    'name' => 'required|string|max:255',
    'email' => 'nullable|email|max:255',
    'mobile_number' => 'nullable|numeric|digits:10',
]
```

### Pattern 3: User/Customer with Authentication
```php
// Users, Customers with login
[
    'name' => 'required|string|max:255',
    'email' => 'required|email|max:255|unique:table,email',
    'mobile_number' => 'required|numeric|digits:10',
    'password' => 'required|string|min:8|max:16|regex:/pattern/',
    'status' => 'required|in:0,1',
]
```

### Pattern 4: Complex Transaction Data
```php
// Customer Insurances, Claims, Quotations
[
    // Foreign key relationships
    'customer_id' => 'required|exists:customers,id',
    'branch_id' => 'required|exists:branches,id',

    // Date fields with format
    'issue_date' => 'required|date_format:d/m/Y',
    'expired_date' => 'required|date_format:d/m/Y',

    // Numeric fields with min/max
    'premium_amount' => 'nullable|numeric|min:0',
    'gst' => 'nullable|numeric|min:0',

    // Enum fields
    'insurance_type' => 'required|in:Health,Vehicle',

    // Optional text fields
    'remarks' => 'nullable|string|max:1000',
]
```

### Pattern 5: Conditional Validation
```php
// Customer document requirements based on type
[
    'type' => 'required|in:Retail,Corporate',
    'pan_card_number' => 'required_if:type,Retail|nullable|string|max:10',
    'gst_number' => 'required_if:type,Corporate|nullable|string|max:15',
]
```

### Pattern 6: Array/Nested Validation
```php
// Quotations with multiple company quotes
[
    'companies' => 'nullable|array',
    'companies.*.insurance_company_id' => 'required_with:companies|exists:insurance_companies,id',
    'companies.*.policy_type' => 'required_with:companies|in:Comprehensive,Own Damage,Third Party',
    'companies.*.idv_vehicle' => 'required_with:companies|numeric|min:10000|max:10000000',
]
```

---

## PHP Code Examples

### Controller Validation Example
```php
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255|unique:branches,name',
        'email' => 'nullable|email|max:255',
    ]);

    // Process validated data
    $branch = Branch::create($request->only(['name', 'email', 'mobile_number']));

    return response()->json(['success' => true, 'data' => $branch], 201);
}
```

### Form Request Validation Example
```php
class StoreBrokerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile_number' => 'nullable|numeric|digits:10',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Broker name is required.',
            'mobile_number.digits' => 'Mobile number must be exactly 10 digits.',
        ];
    }
}
```

### Service-Based Validation Example
```php
class CustomerInsuranceService
{
    public function getStoreValidationRules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'policy_no' => 'required',
            'final_premium_with_gst' => 'required|numeric|min:0',
            // ... more rules
        ];
    }
}

// In Controller
public function store(Request $request)
{
    $rules = $this->service->getStoreValidationRules();
    $request->validate($rules);

    $data = $this->service->prepareStorageData($request);
    $insurance = $this->service->createCustomerInsurance($data);

    return response()->json(['success' => true, 'data' => $insurance], 201);
}
```

### Date Transformation Example
```php
class StoreCustomerRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $data = [];

        // Convert DD/MM/YYYY to YYYY-MM-DD
        foreach (['date_of_birth', 'wedding_anniversary_date'] as $dateField) {
            if ($this->has($dateField) && $this->$dateField) {
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $this->$dateField)) {
                    $dateParts = explode('/', $this->$dateField);
                    $data[$dateField] = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                }
            }
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
```

---

## Summary Statistics

### Total Modules: 19
- Branches
- Brokers
- Reference Users
- Insurance Companies
- Addon Covers
- Customers
- Customer Insurances (48+ fields)
- Claims
- Fuel Types
- Policy Types
- Premium Types
- Relationship Managers
- Quotations (30+ fields)
- Users/Admins
- Roles
- Permissions
- App Settings (80+ settings)
- Notification Templates
- Family Groups

### Validation Rule Types Used:
- **String**: required, string, max, min, between
- **Numeric**: numeric, integer, digits, min, max
- **Date**: date, date_format, before, after, before_or_equal
- **Relationship**: exists, unique
- **Conditional**: required_if, required_with, nullable
- **Enum**: in
- **File**: file, max, mimetypes
- **Boolean**: boolean
- **Array**: array, nested validation (companies.*.field)
- **Regex**: Custom patterns for phone, password, etc.

### Common Field Patterns:
- **Name**: `required|string|max:255`
- **Email**: `required|email|max:255|unique:table,email`
- **Mobile**: `required|numeric|digits:10`
- **Status**: `required|in:0,1`
- **Date**: `required|date_format:d/m/Y`
- **Amount**: `nullable|numeric|min:0`
- **Foreign Key**: `required|exists:table,id`

---

**End of Validation Rules Reference**
