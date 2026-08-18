<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisputeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'order_id' => $this->order_id,
            'order' => OrderResource::make($this->whenLoaded('order')),
            'reason' => $this->reason,
            'description' => $this->description,
            'status' => $this->status,
            'resolved_by' => $this->resolved_by,
            'resolution_note' => $this->resolution_note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
