<?php

namespace App\Http\Requests\Api;

use App\Models\VendorApplication;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateVendorApplicationRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('vendor-application') ?? VendorApplication::find($this->route('id')));
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(['pending', 'approved', 'rejected'])],
        ];
    }
}
