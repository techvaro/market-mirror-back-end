<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class UpdateChangeRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
