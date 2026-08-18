<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class StorePaymentRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'max:10', 'default:NGN'],
            'method' => ['required', 'string', Rule::in(['paystack', 'card', 'bank_transfer', 'cod', 'cash'])],
            'reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}
