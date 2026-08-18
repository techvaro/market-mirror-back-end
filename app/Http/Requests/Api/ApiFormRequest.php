<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Base API form request. Sanitizes string inputs (strips HTML tags) so that
 * malicious markup never reaches the database or other clients.
 */
abstract class ApiFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function sanitizeValue(mixed $value): mixed
    {
        if (is_string($value)) {
            return trim(Str::of($value)->stripTags()->toString());
        }

        if (is_array($value)) {
            return array_map(fn ($item) => $this->sanitizeValue($item), $value);
        }

        return $value;
    }

    /**
     * Return validated data with all string values sanitized against XSS.
     */
    public function sanitized(): array
    {
        return $this->sanitizeValue($this->validated());
    }
}
