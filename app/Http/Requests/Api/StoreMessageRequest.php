<?php

namespace App\Http\Requests\Api;

class StoreMessageRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'conversation_id' => ['required', 'integer', 'exists:chat_conversations,id'],
            'text' => ['required', 'string', 'max:5000'],
            'image_url' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
