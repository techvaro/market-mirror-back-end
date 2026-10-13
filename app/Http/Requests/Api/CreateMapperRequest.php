<?php

namespace App\Http\Requests\Api;

use App\Models\Mapper;
use Illuminate\Support\Facades\Gate;

class CreateMapperRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage', Mapper::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'employee_id' => ['required', 'string', 'unique:mappers,employee_id'],
            'assigned_market' => ['required', 'string', 'max:255'],
            'assigned_region' => ['required', 'string', 'max:255'],
            'supervisor' => ['nullable', 'string', 'max:255'],
        ];
    }
}
