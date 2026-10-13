<?php

namespace App\Http\Requests\Api;

class VerifyFlutterwavePaymentRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'transaction_id' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+$/'],
        ];
    }
}
