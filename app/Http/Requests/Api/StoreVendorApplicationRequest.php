<?php

namespace App\Http\Requests\Api;

class StoreVendorApplicationRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'shop_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'category' => ['required', 'string', 'max:100'],
            'market' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
