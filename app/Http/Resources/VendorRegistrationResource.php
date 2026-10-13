<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorRegistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_name' => $this->vendor_name,
            'owner_name' => $this->owner_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'business_category' => $this->business_category,
            'market' => $this->market,
            'shop_number' => $this->shop_number,
            'mapper_id' => $this->mapper_id,
            'mapper' => MapperResource::make($this->whenLoaded('mapper')),
            'mapper_name' => $this->mapper_name,
            'current_status' => $this->current_status,
            'approval_status' => $this->approval_status,
            'review_notes' => $this->review_notes,
            'reviewed_at' => $this->reviewed_at,
            'reviewed_by' => $this->reviewed_by,
            'photos' => $this->photos,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}
