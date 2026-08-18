<?php

namespace App\Http\Requests\Api;

use App\Models\Review;
use Illuminate\Support\Facades\Gate;

class ReplyReviewRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('reply', $this->route('review') ?? Review::find($this->route('id')));
    }

    public function rules(): array
    {
        return [
            'reply' => ['required', 'string', 'max:2000'],
        ];
    }
}
