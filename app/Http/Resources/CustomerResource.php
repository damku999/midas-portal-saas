<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile_number' => $this->mobile_number,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'wedding_anniversary_date' => $this->wedding_anniversary_date?->format('Y-m-d'),
            'engagement_anniversary_date' => $this->engagement_anniversary_date?->format('Y-m-d'),
            'type' => $this->type,
            'status' => $this->status,
            'pan_card_number' => $this->pan_card_number,
            'aadhar_card_number' => $this->aadhar_card_number,
            'gst_number' => $this->gst_number,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
