<?php

namespace App\Http\Requests\Api;

use App\Models\Dispute;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateDisputeRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('dispute') ?? Dispute::find($this->route('id')));
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(['Under Review', 'Resolved', 'Closed'])],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
