<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => UserResource::make($this->whenLoaded('user')),
            'shop_name' => $this->shop_name,
            'building_name' => $this->building_name,
            'shop_number' => $this->shop_number,
            'shop_address' => $this->shop_address,
            'owner_name' => $this->owner_name,
            'phone' => $this->phone,
            'category' => $this->category,
            'market' => $this->market,
            'description' => $this->description,
            'has_id_document' => filled($this->id_document_path),
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
