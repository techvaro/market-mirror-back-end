<?php

namespace App\Http\Requests\Api;

class StoreReviewRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'integer', 'exists:shops,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'text' => ['required', 'string', 'max:2000'],
        ];
    }
}
