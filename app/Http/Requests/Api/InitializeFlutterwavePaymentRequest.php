<?php

namespace App\Http\Requests\Api;

class InitializeFlutterwavePaymentRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            // These values identify the payer only. Amount is deliberately not accepted.
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'customer_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
