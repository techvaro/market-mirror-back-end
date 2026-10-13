<?php

namespace App\Http\Requests\Api;

class StoreConversationRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shop_id' => 'required|integer|exists:shops,id',
        ];
    }
}
