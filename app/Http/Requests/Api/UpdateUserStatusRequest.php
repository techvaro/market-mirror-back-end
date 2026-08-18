<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateUserStatusRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage', User::class);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(['active', 'suspended', 'pending'])],
        ];
    }
}
