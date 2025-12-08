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
            'username' => [
                'required',
                'string',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
            ],
            'remember' => [
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
            'username.required' => 'Username atau email wajib diisi.',
            'username.string' => 'Username atau email harus berupa teks.',
            'username.max' => 'Username atau email maksimal 255 karakter.',

            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',

            'remember.boolean' => 'Remember me harus berupa true atau false.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'username' => 'username atau email',
            'password' => 'password',
            'remember' => 'remember me',
        ];
    }
}
