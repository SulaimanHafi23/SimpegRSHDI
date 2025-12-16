<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'worker_id' => [
                'nullable',
                'string',
                Rule::exists('workers', 'id')
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password' => $userId ? 'nullable|string|min:8' : 'required|string|min:8',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ];
    }

    public function attributes(): array
    {
        return [
            'worker_id' => 'Pekerja',
            'email' => 'Email',
            'password' => 'Password',
            'is_active' => 'Status Aktif',
            'photo' => 'Foto',
        ];
    }
}
