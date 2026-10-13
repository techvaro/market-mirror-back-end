<?php

namespace App\Http\Requests\Api;

class StoreChangeRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'vendor';
    }

    public function rules(): array
    {
        return [
            'entity_type' => ['required', 'in:shop,product'],
            'entity_id' => ['required', 'integer'],
            'entity_name' => ['required', 'string', 'max:255'],
            'field_changes' => ['required', 'array', 'min:1'],
            'field_changes.*.field' => ['required', 'string', 'max:255'],
            'field_changes.*.oldValue' => ['nullable'],
            'field_changes.*.newValue' => ['nullable'],
            'reason' => ['required', 'string', 'max:5000'],
        ];
    }
}
