<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopLocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'mapper_id' => $this->mapper_id,
            'mapper' => MapperResource::make($this->whenLoaded('mapper')),
            'vendor_name' => $this->vendor_name,
            'market' => $this->market,
            'street' => $this->street,
            'shop_block' => $this->shop_block,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'accuracy' => $this->accuracy,
            'status' => $this->status,
            'mapper_name' => $this->mapper_name,
            'photos' => $this->photos,
            'created_at' => $this->created_at,
        ];
    }
}
