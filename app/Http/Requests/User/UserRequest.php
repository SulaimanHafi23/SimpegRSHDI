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
    // The route parameter for the resource may be named 'user' (resource) or 'id' in some places.
    // Accept either so validation can detect if this is an update request.
    $userId = $this->route('user') ?? $this->route('id');

        return [
            'worker_id' => [
                $userId ? 'nullable' : 'required',
                'uuid',
                Rule::exists('workers', 'id')
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($userId)
            ],
            // Require password confirmation when password is provided / required
            'password' => $userId ? 'nullable|string|min:8|confirmed' : 'required|string|min:8|confirmed',
            'password_confirmation' => $userId ? 'nullable|string|min:8' : 'required|string|min:8',
            'is_active' => 'boolean',
            // Allow larger uploads (max in kilobytes). Keep validation to prevent huge files
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:10240', // 10 MB
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:roles,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'worker_id' => 'Pekerja',
            'email' => 'Email',
            'password' => 'Password',
            'username' => 'Username',
            'password_confirmation' => 'Konfirmasi Password',
            'is_active' => 'Status Aktif',
            'photo' => 'Foto',
        ];
    }

    public function messages(): array
    {
        return [
            'worker_id.required'              => 'Pekerja wajib dipilih.',
            'worker_id.uuid'                  => 'ID Pekerja tidak valid.',
            'worker_id.exists'                => 'Pekerja yang dipilih tidak ditemukan.',
            'email.required'                  => 'Email wajib diisi.',
            'email.email'                     => 'Format email tidak valid.',
            'email.max'                       => 'Email maksimal 255 karakter.',
            'email.unique'                    => 'Email sudah digunakan.',
            'username.required'               => 'Username wajib diisi.',
            'username.max'                    => 'Username maksimal 255 karakter.',
            'username.unique'                 => 'Username sudah digunakan.',
            'password.required'               => 'Password wajib diisi.',
            'password.min'                    => 'Password minimal 8 karakter.',
            'password.confirmed'              => 'Konfirmasi password tidak cocok.',
            'password_confirmation.required'  => 'Konfirmasi password wajib diisi.',
            'password_confirmation.min'       => 'Konfirmasi password minimal 8 karakter.',
            'photo.image'                     => 'Foto harus berupa file gambar.',
            'photo.mimes'                     => 'Foto hanya mendukung format: jpeg, jpg, png.',
            'photo.max'                       => 'Ukuran foto maksimal 10MB.',
            'roles.array'                     => 'Data role tidak valid.',
            'roles.*.integer'                 => 'ID role tidak valid.',
            'roles.*.exists'                  => 'Role yang dipilih tidak ditemukan.',
        ];
    }
}
