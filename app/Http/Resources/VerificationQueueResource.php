<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VerificationQueueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_name' => $this->vendor_name,
            'market' => $this->market,
            'mapper_id' => $this->mapper_id,
            'mapper' => MapperResource::make($this->whenLoaded('mapper')),
            'mapper_name' => $this->mapper_name,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'photos' => $this->photos,
            'documents' => $this->documents,
            'notes' => $this->notes,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
