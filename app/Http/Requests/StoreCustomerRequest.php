<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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

        // Convert date formats from DD/MM/YYYY to YYYY-MM-DD
        foreach (['date_of_birth', 'wedding_anniversary_date', 'engagement_anniversary_date'] as $dateField) {
            if ($this->has($dateField) && $this->$dateField) {
                $dateValue = $this->$dateField;
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateValue)) {
                    $dateParts = explode('/', $dateValue);
                    $data[$dateField] = $dateParts[2].'-'.$dateParts[1].'-'.$dateParts[0];
                }
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
        $rules = [
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
        ];

        if (! empty($this->date_of_birth)) {
            $rules['date_of_birth'] = 'date';
        }

        if (! empty($this->wedding_anniversary_date)) {
            $rules['wedding_anniversary_date'] = 'date';
        }

        if (! empty($this->engagement_anniversary_date)) {
            $rules['engagement_anniversary_date'] = 'date';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
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
        ];
    }
}
