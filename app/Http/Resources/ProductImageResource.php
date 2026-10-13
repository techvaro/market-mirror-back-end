<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->image_url,
            'image_url' => $this->image_url,
            'sort_order' => $this->sort_order,
            'is_primary' => $this->sort_order === 0,
        ];
    }
}
