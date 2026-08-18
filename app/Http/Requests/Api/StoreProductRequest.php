<?php

namespace App\Http\Requests\Api;

use Illuminate\Support\Facades\Gate;

class StoreProductRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', \App\Models\Product::class);
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'integer', 'exists:shops,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'boolean'],
            'color' => ['nullable', 'string', 'max:50'],
        ];
    }
}
