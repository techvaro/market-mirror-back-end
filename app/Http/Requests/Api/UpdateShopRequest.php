<?php

namespace App\Http\Requests\Api;

use App\Models\Shop;
use Illuminate\Support\Facades\Gate;

class UpdateShopRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('shop') ?? Shop::find($this->route('id')));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string'],
            'location' => ['sometimes', 'string', 'max:255'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'market_id' => ['sometimes', 'nullable', 'integer', 'exists:markets,id'],
            'market' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:50'],
            'shop_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'hours' => ['sometimes', 'nullable', 'string', 'max:255'],
            'banner_gradient' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
