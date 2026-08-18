<?php

namespace App\Http\Requests\Api;

class CheckoutRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'delivery_method' => ['required', 'string', 'in:delivery,pickup'],
            'payment_method' => ['sometimes', 'string', 'in:paystack,card,bank_transfer,cod'],
            'address_first_name' => ['nullable', 'string', 'max:255'],
            'address_last_name' => ['nullable', 'string', 'max:255'],
            'address_phone' => ['nullable', 'string', 'max:50'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_state' => ['nullable', 'string', 'max:255'],
            'address_city' => ['nullable', 'string', 'max:255'],
            'items' => ['sometimes', 'array'],
            'items.*.product_id' => ['sometimes', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['sometimes', 'integer', 'min:1', 'max:999'],
        ];
    }
}
