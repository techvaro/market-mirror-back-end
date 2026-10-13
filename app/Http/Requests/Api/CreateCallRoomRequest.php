<?php

namespace App\Http\Requests\Api;

class CreateCallRoomRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'conversation_id' => ['required', 'integer', 'exists:chat_conversations,id'],
            'call_type' => ['required', 'string', 'in:audio,video'],
        ];
    }
}
