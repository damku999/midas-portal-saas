<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrokerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'contact_details' => [
                'email' => $this->email,
                'mobile_number' => $this->mobile_number,
                'address' => $this->address,
            ],
            'license_details' => [
                'license_number' => $this->license_number,
                'license_expiry_date' => $this->license_expiry_date?->format('Y-m-d'),
                'is_license_expiring_soon' => $this->license_expiry_date ? $this->license_expiry_date->diffInDays(now()) <= 30 : null,
                'is_license_expired' => $this->license_expiry_date ? $this->license_expiry_date->isPast() : null,
            ],
            'business_details' => [
                'gst_number' => $this->gst_number,
                'pan_number' => $this->pan_number,
            ],
            'status' => $this->status,
            'statistics' => $this->when($this->relationLoaded('statistics'), [
                'total_policies' => $this->statistics['total_policies'] ?? 0,
                'active_policies' => $this->statistics['active_policies'] ?? 0,
                'total_commission' => $this->statistics['total_commission'] ?? 0,
                'monthly_commission' => $this->statistics['monthly_commission'] ?? 0,
                'commission_percentage' => $this->statistics['commission_percentage'] ?? 0,
            ]),
            'dates' => [
                'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            ],
        ];
    }
}
