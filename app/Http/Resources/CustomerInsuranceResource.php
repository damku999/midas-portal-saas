<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerInsuranceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'policy_number' => $this->policy_number,
            'customer' => [
                'id' => $this->customer?->id,
                'name' => $this->customer?->name,
                'email' => $this->customer?->email,
                'mobile_number' => $this->customer?->mobile_number,
            ],
            'insurance_company' => [
                'id' => $this->insuranceCompany?->id,
                'name' => $this->insuranceCompany?->name,
                'company_code' => $this->insuranceCompany?->company_code,
                'email' => $this->insuranceCompany?->email,
                'contact_number' => $this->insuranceCompany?->contact_number,
            ],
            'policy_type' => [
                'id' => $this->policyType?->id,
                'type' => $this->policyType?->type,
                'description' => $this->policyType?->description,
            ],
            'policy_dates' => [
                'start_date' => $this->start_date?->format('Y-m-d'),
                'expired_date' => $this->expired_date?->format('Y-m-d'),
                'days_to_expiry' => $this->expired_date ? $this->expired_date->diffInDays(now(), false) : null,
                'is_expired' => $this->expired_date ? $this->expired_date->isPast() : null,
                'is_expiring_soon' => $this->expired_date ? $this->expired_date->diffInDays(now()) <= 30 : null,
            ],
            'vehicle_details' => [
                'vehicle_number' => $this->vehicle_number,
                'vehicle_make' => $this->vehicle_make,
                'vehicle_model' => $this->vehicle_model,
                'vehicle_variant' => $this->vehicle_variant,
                'manufacturing_year' => $this->manufacturing_year,
                'registration_year' => $this->registration_year,
                'fuel_type' => $this->fuel_type,
                'rto_code' => $this->rto_code,
                'cc' => $this->cc,
                'seating_capacity' => $this->seating_capacity,
            ],
            'premium_details' => [
                'od_premium' => $this->od_premium ? (float) $this->od_premium : null,
                'tp_premium' => $this->tp_premium ? (float) $this->tp_premium : null,
                'net_premium' => $this->net_premium ? (float) $this->net_premium : null,
                'gst' => $this->gst ? (float) $this->gst : null,
                'total_premium' => $this->total_premium ? (float) $this->total_premium : null,
            ],
            'commission_details' => [
                'od_brokerage' => $this->od_brokerage ? (float) $this->od_brokerage : null,
                'tp_brokerage' => $this->tp_brokerage ? (float) $this->tp_brokerage : null,
                'total_brokerage' => $this->total_brokerage ? (float) $this->total_brokerage : null,
                'commission_percentage' => $this->commission_percentage ? (float) $this->commission_percentage : null,
            ],
            'business_details' => [
                'broker_id' => $this->broker_id,
                'broker' => [
                    'id' => $this->broker?->id,
                    'name' => $this->broker?->name,
                ],
                'branch_id' => $this->branch_id,
                'branch' => [
                    'id' => $this->branch?->id,
                    'name' => $this->branch?->name,
                ],
                'reference_user_id' => $this->reference_user_id,
                'reference_user' => [
                    'id' => $this->referenceUser?->id,
                    'name' => $this->referenceUser?->name,
                ],
            ],
            'status' => $this->status,
            'remarks' => $this->remarks,
            'ncb_percentage' => $this->ncb_percentage,
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
