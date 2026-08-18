<?php

namespace App\Http\Requests\Api;

use Illuminate\Support\Facades\Gate;

class StoreShopRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', \App\Models\Shop::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'market_id' => ['sometimes', 'nullable', 'integer', 'exists:markets,id'],
            'market' => ['required_without:market_id', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'shop_number' => ['nullable', 'string', 'max:50'],
            'hours' => ['nullable', 'string', 'max:255'],
            'banner_gradient' => ['nullable', 'string', 'max:255'],
        ];
    }
}
