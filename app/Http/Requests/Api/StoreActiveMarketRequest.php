<?php

namespace App\Http\Requests\Api;

class StoreActiveMarketRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'market_id' => ['required', 'integer', 'exists:markets,id'],
        ];
    }
}
