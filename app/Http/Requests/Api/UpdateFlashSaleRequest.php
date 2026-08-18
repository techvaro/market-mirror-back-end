<?php

namespace App\Http\Requests\Api;

use App\Models\FlashSale;
use Illuminate\Support\Facades\Gate;

class UpdateFlashSaleRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('flash-sale') ?? FlashSale::find($this->route('id')));
    }

    public function rules(): array
    {
        return [
            'product_id' => ['sometimes', 'nullable', 'integer', 'exists:products,id'],
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'original_price' => ['sometimes', 'numeric', 'min:0'],
            'sale_price' => ['sometimes', 'numeric', 'min:0'],
            'start_time' => ['sometimes', 'date'],
            'end_time' => ['sometimes', 'date', 'after:start_time'],
            'stock_count' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
