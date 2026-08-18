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
            'business_category' => $this->business_category,
            'market' => $this->market,
            'mapper_id' => $this->mapper_id,
            'mapper' => MapperResource::make($this->whenLoaded('mapper')),
            'mapper_name' => $this->mapper_name,
            'current_status' => $this->current_status,
            'approval_status' => $this->approval_status,
            'photos' => $this->photos,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}
