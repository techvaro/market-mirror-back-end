<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'state_id' => $this->state_id,
            'name' => $this->name,
            'description' => $this->description,
            'location' => $this->location,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'city' => $this->city,
            'state' => $this->state,
            'shop_count' => $this->shop_count,
            'category' => $this->category,
            'banner_gradient' => $this->banner_gradient,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
