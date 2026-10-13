<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'status' => $this->status,
            'must_change_password' => (bool) $this->must_change_password,
            'location' => $this->location,
            'avatar_url' => $this->avatar_url,
            'profile_image_url' => $this->profile_image_url,
            'employee_id' => $this->employee_id,
            'region' => $this->region,
            'active_market_id' => $this->active_market_id,
            'active_market' => MarketResource::make($this->whenLoaded('activeMarket')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'shops' => ShopResource::collection($this->whenLoaded('shops')),
            'mapper_profile' => MapperResource::make($this->whenLoaded('mapperProfile')),
            'vendor_application' => VendorApplicationResource::make($this->whenLoaded('vendorApplication')),
        ];
    }
}
