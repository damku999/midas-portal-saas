<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationCompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company' => [
                'id' => $this->insuranceCompany?->id,
                'name' => $this->insuranceCompany?->name,
                'code' => $this->insuranceCompany?->company_code,
                'status' => $this->insuranceCompany?->status,
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
            ],
            'policy_details' => [
                'policy_number' => $this->policy_number,
                'start_date' => $this->start_date?->format('Y-m-d'),
                'end_date' => $this->end_date?->format('Y-m-d'),
            ],
            'status' => $this->status,
            'remarks' => $this->remarks,
            'selected' => (bool) $this->selected,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
