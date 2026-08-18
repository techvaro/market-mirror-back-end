<?php

namespace App\Http\Requests\Api;

use Illuminate\Support\Facades\Gate;

class StoreFlashSaleRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', \App\Models\FlashSale::class);
    }

    public function rules(): array
    {
        return [
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'original_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'stock_count' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
