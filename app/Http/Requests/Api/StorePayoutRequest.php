<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class StorePayoutRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['sometimes', 'string', Rule::in(['bank_transfer', 'mobile_money', 'paypal'])],
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
