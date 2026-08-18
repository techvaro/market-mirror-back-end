<?php

namespace App\Http\Requests\Api;

use App\Models\Mapper;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateMapperRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage', $this->route('mapper') ?? Mapper::find($this->route('id')));
    }

    public function rules(): array
    {
        return [
            'assigned_market' => ['sometimes', 'string', 'max:255'],
            'assigned_region' => ['sometimes', 'string', 'max:255'],
            'mapper_status' => ['sometimes', 'string', Rule::in(['active', 'inactive', 'suspended'])],
            'supervisor' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'name' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
