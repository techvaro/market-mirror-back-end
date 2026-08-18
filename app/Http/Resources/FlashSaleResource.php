<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlashSaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => ProductResource::make($this->whenLoaded('product')),
            'name' => $this->name,
            'original_price' => (int) $this->original_price,
            'sale_price' => (int) $this->sale_price,
            'discount_percentage' => $this->discount_percentage,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'sold_count' => $this->sold_count,
            'stock_count' => $this->stock_count,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
