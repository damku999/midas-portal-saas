<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class SecureFormRequest extends FormRequest
{
    /**
     * Common security-focused validation rules
     */
    protected function getSecureStringRules(int $maxLength = 255, bool $allowHtml = false): string
    {
        $rules = ['string', "max:{$maxLength}"];

        if (! $allowHtml) {
            $rules[] = 'regex:/^[^<>]*$/'; // No HTML tags
        }

        return implode('|', $rules);
    }

    /**
     * Get secure email validation rules
     */
    protected function getSecureEmailRules(?string $table = null, string $column = 'email', $except = null): string
    {
        $rules = ['email:rfc,dns', 'max:255'];

        if ($table) {
            $uniqueRule = "unique:{$table},{$column}";
            if ($except) {
                $uniqueRule .= ",{$except}";
            }
            $rules[] = $uniqueRule;
        }

        return implode('|', $rules);
    }

    /**
     * Get secure phone number validation rules
     */
    protected function getSecurePhoneRules(): string
    {
        return 'regex:/^[\+]?[0-9\s\-\(\)]{10,15}$/';
    }

    /**
     * Get secure numeric validation rules
     */
    protected function getSecureNumericRules(?int $min = null, ?int $max = null): string
    {
        $rules = ['numeric'];

        if ($min !== null) {
            $rules[] = "min:{$min}";
        }

        if ($max !== null) {
            $rules[] = "max:{$max}";
        }

        return implode('|', $rules);
    }

    /**
     * Get secure file upload validation rules
     */
    protected function getSecureFileRules(?array $allowedTypes = null, int $maxSize = 2048): string
    {
        $allowedTypes = $allowedTypes ?? config('security.file_uploads.allowed_extensions', ['jpg', 'jpeg', 'png', 'pdf']);
        $maxSize = min($maxSize, config('security.file_uploads.max_size', 10240));

        return "file|max:{$maxSize}|mimes:".implode(',', $allowedTypes);
    }

    /**
     * Get secure date validation rules
     */
    protected function getSecureDateRules(bool $allowFuture = true, bool $allowPast = true): string
    {
        $rules = ['date'];

        if (! $allowFuture) {
            $rules[] = 'before_or_equal:today';
        }

        if (! $allowPast) {
            $rules[] = 'after_or_equal:today';
        }

        return implode('|', $rules);
    }

    /**
     * Sanitize input data before validation
     */
    protected function prepareForValidation()
    {
        $input = $this->all();
        $sanitized = [];

        foreach ($input as $key => $value) {
            if (is_string($value)) {
                // Trim whitespace
                $value = trim($value);

                // Convert empty strings to null
                if ($value === '') {
                    $value = null;
                }

                // Basic XSS protection for non-HTML fields
                if (! $this->isHtmlField($key)) {
                    $value = strip_tags($value);
                }

                $sanitized[$key] = $value;
            } else {
                $sanitized[$key] = $value;
            }
        }

        $this->replace($sanitized);
    }

    /**
     * Define which fields are allowed to contain HTML
     */
    protected function isHtmlField(string $field): bool
    {
        $htmlFields = $this->getAllowedHtmlFields();

        return in_array($field, $htmlFields);
    }

    /**
     * Override this method to define fields that can contain HTML
     */
    protected function getAllowedHtmlFields(): array
    {
        return ['description', 'notes', 'content', 'details'];
    }

    /**
     * Enhanced authorization with permission checking
     */
    public function authorize(): bool
    {
        // Check if user is authenticated
        if (! auth()->check()) {
            return false;
        }

        // Check specific permission if defined
        if ($permission = $this->getRequiredPermission()) {
            return auth()->user()->can($permission);
        }

        // Default: allow if authenticated
        return true;
    }

    /**
     * Override this method to define required permission
     */
    protected function getRequiredPermission(): ?string
    {
        return null;
    }

    /**
     * Enhanced error messages with security context
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            '*.regex' => 'The :attribute contains invalid characters.',
            '*.max' => 'The :attribute may not be greater than :max characters.',
            '*.min' => 'The :attribute must be at least :min characters.',
            '*.mimes' => 'The :attribute must be a file of type: :values.',
            '*.unique' => 'The :attribute has already been taken.',
            '*.exists' => 'The selected :attribute is invalid.',
            '*.before_or_equal' => 'The :attribute must be a date before or equal to :date.',
            '*.after_or_equal' => 'The :attribute must be a date after or equal to :date.',
        ]);
    }

    /**
     * Custom attributes for better error messages
     */
    public function attributes(): array
    {
        return [
            'mobile_number' => 'mobile number',
            'pan_card_number' => 'PAN card number',
            'aadhar_card_number' => 'Aadhar card number',
            'gst_number' => 'GST number',
            'customer_insurance_id' => 'policy',
            'insurance_company_id' => 'insurance company',
            'quotation_id' => 'quotation',
            'policy_type_id' => 'policy type',
            'premium_type_id' => 'premium type',
            'fuel_type_id' => 'fuel type',
        ];
    }

    /**
     * Get sanitized and validated data
     */
    public function getSanitizedData(): array
    {
        $data = $this->validated();

        // Additional sanitization for specific field types
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Trim whitespace again after validation
                $data[$key] = trim($value);
            }
        }

        return $data;
    }
}
