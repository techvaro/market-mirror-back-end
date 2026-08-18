<?php

namespace App\Http\Requests\Api;

use App\Models\ShopLocation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateShopLocationRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('shop-location') ?? ShopLocation::find($this->route('id')));
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(['pending', 'approved', 'rejected', 're_mapping_requested'])],
            'photos' => ['sometimes', 'nullable', 'array'],
            'photos.*' => ['string', 'max:2048'],
        ];
    }
}
