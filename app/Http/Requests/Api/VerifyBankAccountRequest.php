<?php

namespace App\Http\Requests\Api;

class VerifyBankAccountRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_code' => ['required', 'string', 'max:20'],
            'account_number' => ['required', 'string', 'digits:10'],
        ];
    }
}
