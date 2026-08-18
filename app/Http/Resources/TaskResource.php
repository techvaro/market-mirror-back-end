<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mapper_id' => $this->mapper_id,
            'mapper' => MapperResource::make($this->whenLoaded('mapper')),
            'vendor_id' => $this->vendor_id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'market' => $this->market,
            'street' => $this->street,
            'shop_block' => $this->shop_block,
            'priority' => $this->priority,
            'status' => $this->status,
            'daily_target' => $this->daily_target,
            'due_date' => $this->due_date,
            'assigned_at' => $this->assigned_at,
            'completed_at' => $this->completed_at,
            'corrections' => $this->corrections,
            'created_at' => $this->created_at,
        ];
    }
}
