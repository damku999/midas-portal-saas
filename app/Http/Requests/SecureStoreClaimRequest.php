<?php

namespace App\Http\Requests;

class SecureStoreClaimRequest extends SecureFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('claim-create');
    }

    /**
     * Get the required permission for this request.
     */
    protected function getRequiredPermission(): ?string
    {
        return 'claim-create';
    }

    /**
     * Prepare the data for validation with enhanced security.
     */
    protected function prepareForValidation()
    {
        parent::prepareForValidation();

        $data = [];

        // Secure date format conversion for incident date
        if ($this->has('incident_date') && $this->incident_date) {
            $dateValue = $this->incident_date;
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateValue)) {
                $dateParts = explode('/', $dateValue);
                if (checkdate($dateParts[1], $dateParts[0], $dateParts[2])) {
                    $data['incident_date'] = $dateParts[2].'-'.$dateParts[1].'-'.$dateParts[0];
                }
            }
        }

        // Sanitize phone number
        if ($this->has('whatsapp_number')) {
            $data['whatsapp_number'] = preg_replace('/[^0-9+\-\(\)\s]/', '', $this->whatsapp_number);
        }

        // Sanitize claim amount if present
        if ($this->has('claim_amount')) {
            $data['claim_amount'] = preg_replace('/[^0-9.]/', '', $this->claim_amount);
        }

        if (! empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'customer_insurance_id' => [
                'required',
                'exists:customer_insurances,id',
            ],
            'insurance_type' => [
                'required',
                'in:Health,Vehicle,Life,Property',
            ],
            'incident_date' => [
                'required',
                $this->getSecureDateRules(false, true), // No future dates
                'after:'.now()->subYears(10)->format('Y-m-d'), // Not more than 10 years ago
            ],
            'incident_location' => [
                'nullable',
                $this->getSecureStringRules(500),
            ],
            'description' => [
                'required',
                $this->getSecureStringRules(2000, true), // Allow basic formatting
                'min:10',
            ],
            'claim_amount' => [
                'nullable',
                'numeric',
                'min:1',
                'max:99999999.99', // Reasonable max claim amount
            ],
            'whatsapp_number' => [
                'nullable',
                $this->getSecurePhoneRules(),
                'max:15',
            ],
            'emergency_contact_name' => [
                'nullable',
                $this->getSecureStringRules(255),
            ],
            'emergency_contact_number' => [
                'nullable',
                $this->getSecurePhoneRules(),
                'digits:10',
            ],
            'police_report_number' => [
                'nullable',
                $this->getSecureStringRules(50),
                'regex:/^[A-Z0-9\-\/]+$/', // Alphanumeric with hyphens and slashes
            ],
            'hospital_name' => [
                'required_if:insurance_type,Health',
                'nullable',
                $this->getSecureStringRules(255),
            ],
            'doctor_name' => [
                'required_if:insurance_type,Health',
                'nullable',
                $this->getSecureStringRules(255),
            ],
            'vehicle_registration_number' => [
                'required_if:insurance_type,Vehicle',
                'nullable',
                'string',
                'max:20',
                'regex:/^[A-Z]{2}[0-9]{2}[A-Z]{1,2}[0-9]{4}$/', // Indian vehicle number format
            ],
            'garage_name' => [
                'required_if:insurance_type,Vehicle',
                'nullable',
                $this->getSecureStringRules(255),
            ],
            'supporting_documents' => [
                'nullable',
                'array',
                'max:10', // Limit number of files
            ],
            'supporting_documents.*' => [
                $this->getSecureFileRules(['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'], 5120), // 5MB per file
            ],
        ];
    }

    /**
     * Enhanced error messages for claim creation.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'customer_insurance_id.required' => 'Please select a valid insurance policy.',
            'customer_insurance_id.exists' => 'The selected insurance policy is invalid.',
            'insurance_type.required' => 'Insurance type is required.',
            'insurance_type.in' => 'Insurance type must be Health, Vehicle, Life, or Property.',
            'incident_date.required' => 'Incident date is required.',
            'incident_date.before_or_equal' => 'Incident date cannot be in the future.',
            'incident_date.after' => 'Incident date cannot be more than 10 years ago.',
            'description.required' => 'Incident description is required.',
            'description.min' => 'Please provide a detailed description (at least 10 characters).',
            'claim_amount.numeric' => 'Claim amount must be a valid number.',
            'claim_amount.min' => 'Claim amount must be greater than 0.',
            'claim_amount.max' => 'Claim amount exceeds maximum allowed value.',
            'whatsapp_number.regex' => 'Please provide a valid WhatsApp number.',
            'emergency_contact_number.digits' => 'Emergency contact number must be exactly 10 digits.',
            'police_report_number.regex' => 'Police report number contains invalid characters.',
            'hospital_name.required_if' => 'Hospital name is required for health insurance claims.',
            'doctor_name.required_if' => 'Doctor name is required for health insurance claims.',
            'vehicle_registration_number.required_if' => 'Vehicle registration number is required for vehicle insurance claims.',
            'vehicle_registration_number.regex' => 'Please provide a valid vehicle registration number.',
            'garage_name.required_if' => 'Garage name is required for vehicle insurance claims.',
            'supporting_documents.max' => 'You can upload maximum 10 supporting documents.',
        ]);
    }

    /**
     * Fields that are allowed to contain basic formatting
     */
    protected function getAllowedHtmlFields(): array
    {
        return ['description'];
    }
}
