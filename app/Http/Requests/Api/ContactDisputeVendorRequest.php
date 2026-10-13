<?php

namespace App\Http\Requests\Api;

class ContactDisputeVendorRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:2000'],
        ];
    }
}
