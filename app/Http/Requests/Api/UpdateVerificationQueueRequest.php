<?php

namespace App\Http\Requests\Api;

use App\Models\VerificationQueue;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateVerificationQueueRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage', $this->route('verification') ?? VerificationQueue::find($this->route('id')));
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(['pending', 'verified', 'rejected', 'needs_revisit'])],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
