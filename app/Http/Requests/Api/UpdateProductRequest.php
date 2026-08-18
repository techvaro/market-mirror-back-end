<?php

namespace App\Http\Requests\Api;

use App\Models\Product;
use Illuminate\Support\Facades\Gate;

class UpdateProductRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('product') ?? Product::find($this->route('id')));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'category' => ['sometimes', 'string', 'max:100'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'boolean'],
            'color' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }
}
