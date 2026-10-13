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
            'vendor_name' => ['sometimes', 'string', 'max:255'],
            'owner_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'business_category' => ['sometimes', 'string', 'max:100'],
            'market' => ['sometimes', 'string', 'max:255'],
            'shop_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
