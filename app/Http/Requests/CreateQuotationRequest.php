<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuotationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'vehicle_number' => 'nullable|string|max:20',
            'make_model_variant' => 'required|string|max:255',
            'rto_location' => 'required|string|max:255',
            'manufacturing_year' => 'required|integer|min:1980|max:'.(date('Y') + 1),
            'cubic_capacity_kw' => 'required|integer|min:1',
            'seating_capacity' => 'required|integer|min:1|max:50',
            'fuel_type' => 'required|in:Petrol,Diesel,CNG,Electric,Hybrid',
            'ncb_percentage' => 'nullable|numeric|min:0|max:50',
            'whatsapp_number' => 'nullable|string|regex:/^[6-9]\d{9}$/',
            'notes' => 'nullable|string|max:1000',
            // Quotation-level policy and IDV fields (optional, as they can be company-specific too)
            'policy_type' => 'nullable|in:Comprehensive,Own Damage,Third Party',
            'policy_tenure_years' => 'nullable|integer|in:1,2,3',
            'idv_vehicle' => 'nullable|numeric|min:10000|max:10000000',
            'idv_trailer' => 'nullable|numeric|min:0',
            'idv_cng_lpg_kit' => 'nullable|numeric|min:0',
            'idv_electrical_accessories' => 'nullable|numeric|min:0',
            'idv_non_electrical_accessories' => 'nullable|numeric|min:0',
            'total_idv' => 'nullable|numeric|min:0',
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
        ];

        // Add dynamic addon field validations
        $addonCovers = \App\Models\AddonCover::getOrdered(1);
        foreach ($addonCovers as $addonCover) {
            $slug = \Str::slug($addonCover->name, '_');
            $rules["companies.*.addon_{$slug}"] = 'nullable|numeric|min:0';
            $rules["companies.*.addon_{$slug}_note"] = 'nullable|string|max:100';
            $rules["companies.*.addon_{$slug}_selected"] = 'nullable|string|in:0,1';
        }

        // Accept addon_covers_breakdown from frontend if present
        $rules['companies.*.addon_covers_breakdown'] = 'nullable|array';

        return $rules;
    }

    public function messages(): array
    {
        return [
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
            // Company-level coverage validation messages
            'companies.*.policy_type.required_with' => 'Policy type is required for each company quote.',
            'companies.*.policy_type.in' => 'Please select a valid policy type for each company quote.',
            'companies.*.policy_tenure_years.required_with' => 'Policy tenure is required for each company quote.',
            'companies.*.policy_tenure_years.in' => 'Policy tenure must be 1, 2, or 3 years for each company quote.',
            'companies.*.idv_vehicle.required_with' => 'Vehicle IDV is required for each company quote.',
            'companies.*.idv_vehicle.min' => 'Vehicle IDV must be at least ₹10,000.',
            'companies.*.idv_vehicle.max' => 'Vehicle IDV cannot exceed ₹1,00,00,000.',
            'whatsapp_number.regex' => 'Please enter a valid 10-digit mobile number.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }

    /**
     * Prepare the data for validation by transforming addon fields into addon_covers_breakdown
     */
    protected function prepareForValidation(): void
    {
        $companies = $this->input('companies', []);
        $addonCovers = \App\Models\AddonCover::getOrdered(1);

        foreach ($companies as $index => $companyData) {
            $addonBreakdown = [];

            foreach ($addonCovers as $addonCover) {
                $slug = \Str::slug($addonCover->name, '_');
                $addonKey = "addon_{$slug}";
                $noteKey = "{$addonKey}_note";
                $selectedKey = "{$addonKey}_selected";

                // Check if addon is selected (either has selected flag = 1 OR has value OR has note)
                $isSelected = ($companyData[$selectedKey] ?? '0') === '1'
                    || ! empty($companyData[$addonKey])
                    || ! empty($companyData[$noteKey]);

                if ($isSelected) {
                    $addonBreakdown[$addonCover->name] = [
                        'price' => $companyData[$addonKey] ?? 0,
                        'note' => $companyData[$noteKey] ?? '',
                    ];
                }

                // Remove individual addon fields from company data
                unset($companies[$index][$addonKey]);
                unset($companies[$index][$noteKey]);
                unset($companies[$index][$selectedKey]);
            }

            $companies[$index]['addon_covers_breakdown'] = $addonBreakdown;
        }

        $this->merge(['companies' => $companies]);
    }
}
