<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class StoreStateRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('states', 'name')->ignore($this->route('id'))],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
