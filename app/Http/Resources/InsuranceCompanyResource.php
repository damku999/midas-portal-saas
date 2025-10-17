<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InsuranceCompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company_code' => $this->company_code,
            'contact_details' => [
                'email' => $this->email,
                'contact_number' => $this->contact_number,
                'address' => $this->address,
            ],
            'business_details' => [
                'gst_number' => $this->gst_number,
                'pan_number' => $this->pan_number,
            ],
            'status' => $this->status,
            'statistics' => $this->when($this->relationLoaded('statistics'), [
                'total_policies' => $this->statistics['total_policies'] ?? 0,
                'active_policies' => $this->statistics['active_policies'] ?? 0,
                'expired_policies' => $this->statistics['expired_policies'] ?? 0,
                'total_premium' => $this->statistics['total_premium'] ?? 0,
                'total_brokerage' => $this->statistics['total_brokerage'] ?? 0,
            ]),
            'dates' => [
                'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            ],
        ];
    }
}
