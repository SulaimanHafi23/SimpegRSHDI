<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => [
                'required',
                'string',
            ],
            'password' => [
                'required',
                'string',
            ],
            'remember_me' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'login.required' => 'Username atau email harus diisi.',
            'password.required' => 'Password harus diisi.',
            'remember_me.boolean' => 'Remember me harus berupa true atau false.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'login' => 'username atau email',
            'password' => 'password',
            'remember_me' => 'remember me',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox value to boolean
        $this->merge([
            'remember_me' => $this->has('remember_me') ?
                filter_var($this->remember_me, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false :
                false
        ]);
    }
}
