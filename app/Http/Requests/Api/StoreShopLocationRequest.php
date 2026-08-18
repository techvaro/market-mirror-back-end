<?php

namespace App\Http\Requests\Api;

use App\Models\ShopLocation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreShopLocationRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', ShopLocation::class);
    }

    public function rules(): array
    {
        return [
            'vendor_name' => ['required', 'string', 'max:255'],
            'market' => ['required', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'shop_block' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['required', 'string', Rule::in(['high', 'medium', 'low'])],
            'mapper_name' => ['nullable', 'string', 'max:255'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['string', 'max:2048'],
        ];
    }
}
