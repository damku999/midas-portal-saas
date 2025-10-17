<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClaimRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $data = [];

        // Convert date format from DD/MM/YYYY to YYYY-MM-DD
        if ($this->has('incident_date') && $this->incident_date) {
            $dateValue = $this->incident_date;
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateValue)) {
                $dateParts = explode('/', $dateValue);
                $data['incident_date'] = $dateParts[2].'-'.$dateParts[1].'-'.$dateParts[0];
            }
        }

        if (! empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_insurance_id' => 'required|exists:customer_insurances,id',
            'insurance_type' => 'required|in:Health,Vehicle',
            'incident_date' => 'required|date|before_or_equal:today',
            'description' => 'nullable|string|max:1000',
            'whatsapp_number' => 'nullable|string|regex:/^[+]?[0-9\s\-\(\)]+$/|max:15',
            'send_email_notifications' => 'boolean',
            'status' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
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
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'customer_insurance_id' => 'policy/insurance',
            'insurance_type' => 'insurance type',
            'incident_date' => 'incident date',
            'whatsapp_number' => 'WhatsApp number',
            'send_email_notifications' => 'email notifications',
        ];
    }
}
