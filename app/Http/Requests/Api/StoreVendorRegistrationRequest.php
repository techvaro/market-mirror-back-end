<?php

namespace App\Http\Requests\Api;

use App\Models\VendorRegistration;
use Illuminate\Support\Facades\Gate;

class StoreVendorRegistrationRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', VendorRegistration::class);
    }

    public function rules(): array
    {
        return [
            'vendor_name' => ['required', 'string', 'max:255'],
            'business_category' => ['required', 'string', 'max:100'],
            'market' => ['required', 'string', 'max:255'],
            'mapper_name' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['string', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
