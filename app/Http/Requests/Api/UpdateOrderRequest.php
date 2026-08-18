<?php

namespace App\Http\Requests\Api;

use App\Models\Order;
use Illuminate\Support\Facades\Gate;

class UpdateOrderRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('order') ?? Order::find($this->route('id')));
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', 'in:confirmed,packing,out_for_delivery,delivered,cancelled'],
            'cancellation_reason' => ['nullable', 'string'],
        ];
    }
}
