<?php

namespace App\Http\Requests\Api;

use App\Models\VendorRegistration;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateVendorRegistrationRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('vendor-registration') ?? VendorRegistration::find($this->route('id')));
    }

    public function rules(): array
    {
        return [
            'approval_status' => ['sometimes', 'string', Rule::in(['pending', 'approved', 'rejected'])],
            'current_status' => ['sometimes', 'string', Rule::in(['active', 'inactive', 'new'])],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
