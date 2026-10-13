<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class VendorRegisterRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', Rule::in(['vendor'])],
        ];
    }
}
