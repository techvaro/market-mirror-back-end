<?php

namespace App\Http\Requests\Api;

/**
 * Validates the structure of an inbound Flutterwave webhook payload.
 *
 * Signature verification is enforced by the VerifyFlutterwaveWebhook middleware
 * before this request is resolved; these rules only guard the shape of the body.
 */
class FlutterwaveWebhookRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('event') && $this->has('type')) {
            $this->merge(['event' => $this->input('type')]);
        }
    }

    public function rules(): array
    {
        return [
            'event' => ['required', 'string', 'max:100'],
            'data' => ['required', 'array'],
            'data.id' => ['nullable'],
            'data.tx_ref' => ['nullable', 'string', 'max:255'],
            'data.reference' => ['nullable', 'string', 'max:255'],
            'data.status' => ['nullable', 'string', 'max:50'],
            'data.amount' => ['nullable', 'numeric'],
            'data.currency' => ['nullable', 'string', 'max:10'],
        ];
    }
}
