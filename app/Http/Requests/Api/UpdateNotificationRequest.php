<?php

namespace App\Http\Requests\Api;

class UpdateNotificationRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'read' => ['sometimes', 'boolean'],
            'is_read' => ['sometimes', 'boolean'],
        ];
    }
}
