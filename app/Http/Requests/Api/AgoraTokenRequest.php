<?php

namespace App\Http\Requests\Api;

class AgoraTokenRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'channel_name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_!#$%&()+\-:;<=.?@\[\]^{}|~,]+$/'],
            'uid' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_\-]+$/'],
            'role' => ['required', 'string', 'in:publisher,subscriber'],
            'call_type' => ['required', 'string', 'in:voice,video'],
        ];
    }
}
