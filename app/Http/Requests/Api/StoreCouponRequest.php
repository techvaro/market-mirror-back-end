<?php

namespace App\Http\Requests\Api;

use Illuminate\Support\Facades\Gate;

class StoreCouponRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', \App\Models\Coupon::class);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', 'unique:coupons,code'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'discount_type' => ['required', 'string', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'valid_until' => ['required', 'date'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
