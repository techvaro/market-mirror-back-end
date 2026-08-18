<?php

namespace App\Http\Requests\Api;

use App\Models\Task;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('task') ?? Task::find($this->route('id')));
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(['pending', 'in_progress', 'completed', 'rejected', 'cancelled'])],
            'corrections' => ['sometimes', 'nullable', 'array'],
            'corrections.*' => ['string', 'max:2000'],
        ];
    }
}
