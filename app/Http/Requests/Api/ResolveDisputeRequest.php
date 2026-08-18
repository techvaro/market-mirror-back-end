<?php

namespace App\Http\Requests\Api;

use App\Actions\Disputes\ResolveDisputeAction;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ResolveDisputeRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('resolve', \App\Models\Dispute::class);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', Rule::in(ResolveDisputeAction::ACTIONS)],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
