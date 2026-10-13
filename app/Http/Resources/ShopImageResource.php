<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url ?? $this->image_url ?? null,
            'image_url' => $this->url ?? $this->image_url ?? null,
            'image_type' => $this->image_type ?? 'gallery',
            'is_primary' => $this->is_primary ?? false,
        ];
    }
}
