<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AuditLogFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route is already protected by role middleware; allow here.
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'action' => ['sometimes', 'string', 'max:50'],
            'user_id' => ['sometimes', 'uuid'],
            'model_type' => ['sometimes', 'string', 'max:100'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date'],
            'search' => ['sometimes', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge(array_filter($this->all(), fn($v) => $v !== null && $v !== ''));
    }
}
