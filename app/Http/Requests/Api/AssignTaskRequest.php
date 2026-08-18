<?php

namespace App\Http\Requests\Api;

use App\Models\Mapper;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AssignTaskRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage', Mapper::class);
    }

    public function rules(): array
    {
        return [
            'mapper_id' => ['required', 'integer', 'exists:mappers,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['vendor_registration', 'shop_mapping', 're_mapping', 'verification_visit'])],
            'market' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'string', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'due_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
