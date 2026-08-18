<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'name' => $this->name,
            'variant' => $this->variant,
            'price' => (int) $this->price,
            'quantity' => (int) $this->quantity,
            'color' => $this->color,
            'shop_name' => $this->shop_name,
            'total' => (int) $this->price * (int) $this->quantity,
        ];
    }
}
