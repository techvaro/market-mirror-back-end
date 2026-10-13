<?php

namespace App\Http\Requests\Api;

class UpdatePayoutBankDetailsRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:255'],
            'bank_code' => ['required', 'string', 'max:20'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['required', 'string', 'digits:10'],
        ];
    }
}
