<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quotation_number' => $this->quotation_number,
            'customer' => [
                'id' => $this->customer?->id,
                'name' => $this->customer?->name,
                'email' => $this->customer?->email,
                'mobile_number' => $this->customer?->mobile_number,
            ],
            'policy_type' => [
                'id' => $this->policyType?->id,
                'type' => $this->policyType?->type,
                'description' => $this->policyType?->description,
            ],
            'vehicle_details' => [
                'vehicle_number' => $this->vehicle_number,
                'vehicle_make' => $this->vehicle_make,
                'vehicle_model' => $this->vehicle_model,
                'vehicle_variant' => $this->vehicle_variant,
                'manufacturing_year' => $this->manufacturing_year,
                'registration_year' => $this->registration_year,
                'fuel_type' => [
                    'id' => $this->fuelType?->id,
                    'type' => $this->fuelType?->type,
                ],
                'rto_code' => $this->rto_code,
                'cc' => $this->cc,
                'seating_capacity' => $this->seating_capacity,
                'vehicle_age' => $this->vehicle_age,
            ],
            'insurance_details' => [
                'ncb_percentage' => $this->ncb_percentage,
                'od_discount' => $this->od_discount,
                'previous_year_ncb' => $this->previous_year_ncb,
            ],
            'status' => $this->status,
            'remarks' => $this->remarks,
            'quotation_companies' => QuotationCompanyResource::collection($this->whenLoaded('quotationCompanies')),
            'dates' => [
                'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            ],
            'audit' => [
                'created_by' => [
                    'id' => $this->createdBy?->id,
                    'name' => $this->createdBy?->name,
                ],
                'updated_by' => [
                    'id' => $this->updatedBy?->id,
                    'name' => $this->updatedBy?->name,
                ],
            ],
        ];
    }
}
