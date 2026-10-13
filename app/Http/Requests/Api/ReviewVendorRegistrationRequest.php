<?php

namespace App\Http\Requests\Api;

use App\Models\VendorRegistration;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ReviewVendorRegistrationRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('review', VendorRegistration::class);
    }

    public function rules(): array
    {
        return [
            'approval_status' => ['required', 'string', Rule::in(['approved', 'rejected', 'changes_requested'])],
            'review_notes' => [
                'nullable',
                'string',
                'max:2000',
                'required_if:approval_status,rejected',
                'required_if:approval_status,changes_requested',
            ],
        ];
    }
}
