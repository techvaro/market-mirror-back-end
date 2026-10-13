<?php

namespace App\Http\Requests\Api;

class VendorOnboardingRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'shop_name' => ['required', 'string', 'max:255'],
            'building_name' => ['required', 'string', 'max:255'],
            'shop_number' => ['required', 'string', 'max:50'],
            'shop_address' => ['required', 'string', 'max:500'],
            'owner_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'category' => ['required', 'string', 'max:100'],
            'market' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'id_file' => ['nullable', 'file', 'mimes:jpeg,png,webp,pdf', 'max:5120'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }
}
