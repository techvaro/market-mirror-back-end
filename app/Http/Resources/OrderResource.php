<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => UserResource::make($this->whenLoaded('user')),
            'order_number' => $this->order_number,
            'status' => $this->status,
            'delivery_method' => $this->delivery_method,
            'payment_method' => $this->payment_method,
            'subtotal' => (int) $this->subtotal,
            'delivery_fee' => (int) $this->delivery_fee,
            'total' => (int) $this->total,
            'estimated_delivery' => $this->estimated_delivery,
            'cancellation_reason' => $this->cancellation_reason,
            'address' => [
                'first_name' => $this->address_first_name,
                'last_name' => $this->address_last_name,
                'phone' => $this->address_phone,
                'street' => $this->address_street,
                'state' => $this->address_state,
                'city' => $this->address_city,
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
