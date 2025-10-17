<?php

namespace App\Http\Requests;

class SecureUpdateCustomerRequest extends SecureFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('customer-edit');
    }

    /**
     * Get the required permission for this request.
     */
    protected function getRequiredPermission(): ?string
    {
        return 'customer-edit';
    }

    /**
     * Prepare the data for validation with enhanced security.
     */
    protected function prepareForValidation()
    {
        parent::prepareForValidation();

        $data = [];

        // Secure date format conversion
        foreach (['date_of_birth', 'wedding_anniversary_date', 'engagement_anniversary_date'] as $dateField) {
            if ($this->has($dateField) && $this->$dateField) {
                $dateValue = $this->$dateField;
                // Strict date format validation
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateValue)) {
                    $dateParts = explode('/', $dateValue);
                    // Validate date components
                    if (checkdate($dateParts[1], $dateParts[0], $dateParts[2])) {
                        $data[$dateField] = $dateParts[2].'-'.$dateParts[1].'-'.$dateParts[0];
                    }
                }
            }
        }

        // Sanitize PAN and Aadhar numbers
        if ($this->has('pan_card_number')) {
            $data['pan_card_number'] = strtoupper(preg_replace('/[^A-Z0-9]/', '', $this->pan_card_number));
        }

        if ($this->has('aadhar_card_number')) {
            $data['aadhar_card_number'] = preg_replace('/[^0-9]/', '', $this->aadhar_card_number);
        }

        if ($this->has('gst_number')) {
            $data['gst_number'] = strtoupper(preg_replace('/[^A-Z0-9]/', '', $this->gst_number));
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
        $customerId = $this->route('customer')?->id ?? $this->route('id');

        return [
            'name' => ['required', $this->getSecureStringRules(255)],
            'email' => ['required', $this->getSecureEmailRules('customers', 'email', $customerId)],
            'mobile_number' => ['required', $this->getSecurePhoneRules(), 'digits:10'],
            'status' => ['required', 'boolean'],
            'type' => ['required', 'in:Retail,Corporate'],

            // PAN card validation for Retail customers
            'pan_card_number' => [
                'required_if:type,Retail',
                'nullable',
                'string',
                'size:10',
                'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', // Valid PAN format
            ],
            'pan_card_path' => [
                'nullable',
                $this->getSecureFileRules(['pdf', 'jpg', 'jpeg', 'png'], 2048),
            ],

            // Aadhar card validation for Retail customers
            'aadhar_card_number' => [
                'required_if:type,Retail',
                'nullable',
                'string',
                'size:12',
                'regex:/^[0-9]{12}$/', // Valid Aadhar format
            ],
            'aadhar_card_path' => [
                'nullable',
                $this->getSecureFileRules(['pdf', 'jpg', 'jpeg', 'png'], 2048),
            ],

            // GST validation for Corporate customers
            'gst_number' => [
                'required_if:type,Corporate',
                'nullable',
                'string',
                'size:15',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', // Valid GST format
            ],
            'gst_path' => [
                'nullable',
                $this->getSecureFileRules(['pdf', 'jpg', 'jpeg', 'png'], 2048),
            ],

            // Date validations
            'date_of_birth' => [
                'nullable',
                $this->getSecureDateRules(false, true), // No future dates
                'before:'.now()->subYears(18)->format('Y-m-d'), // Must be 18+ years old
            ],
            'wedding_anniversary_date' => [
                'nullable',
                $this->getSecureDateRules(false, true), // No future dates
            ],
            'engagement_anniversary_date' => [
                'nullable',
                $this->getSecureDateRules(false, true), // No future dates
            ],
        ];
    }

    /**
     * Enhanced error messages for customer updates.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'name.required' => 'Customer name is required.',
            'name.regex' => 'Customer name contains invalid characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'mobile_number.required' => 'Mobile number is required.',
            'mobile_number.digits' => 'Mobile number must be exactly 10 digits.',
            'mobile_number.regex' => 'Please provide a valid mobile number.',
            'type.required' => 'Customer type is required.',
            'type.in' => 'Customer type must be either Retail or Corporate.',
            'pan_card_number.required_if' => 'PAN card number is required for Retail customers.',
            'pan_card_number.regex' => 'Please provide a valid PAN card number (e.g., ABCDE1234F).',
            'aadhar_card_number.required_if' => 'Aadhar card number is required for Retail customers.',
            'aadhar_card_number.regex' => 'Please provide a valid 12-digit Aadhar card number.',
            'gst_number.required_if' => 'GST number is required for Corporate customers.',
            'gst_number.regex' => 'Please provide a valid GST number.',
            'date_of_birth.before' => 'Customer must be at least 18 years old.',
            'date_of_birth.before_or_equal' => 'Date of birth cannot be in the future.',
            'wedding_anniversary_date.before_or_equal' => 'Wedding anniversary date cannot be in the future.',
            'engagement_anniversary_date.before_or_equal' => 'Engagement anniversary date cannot be in the future.',
        ]);
    }

    /**
     * Fields that are allowed to contain basic formatting
     */
    protected function getAllowedHtmlFields(): array
    {
        return []; // No HTML allowed in customer data
    }
}
