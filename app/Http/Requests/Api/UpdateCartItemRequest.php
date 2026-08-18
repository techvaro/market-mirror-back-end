<?php

namespace App\Http\Requests\Api;

class UpdateCartItemRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'variant' => ['nullable', 'string', 'max:255'],
        ];
    }
}
