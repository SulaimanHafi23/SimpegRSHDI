<?php
// filepath: app/Http/Requests/User/UpdateProfileRequest.php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        $userId = auth()->id();

        return [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'Email',
            'name' => 'Nama',
            'phone' => 'Nomor Telepon',
            'address' => 'Alamat',
            'photo' => 'Foto Profil',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'  => 'Email wajib diisi.',
            'email.email'     => 'Format email tidak valid.',
            'email.max'       => 'Email maksimal 255 karakter.',
            'email.unique'    => 'Email sudah digunakan oleh pengguna lain.',
            'name.max'        => 'Nama maksimal 255 karakter.',
            'phone.max'       => 'Nomor telepon maksimal 20 karakter.',
            'address.max'     => 'Alamat maksimal 500 karakter.',
            'photo.image'     => 'Foto Profil harus berupa file gambar.',
            'photo.mimes'     => 'Foto Profil hanya mendukung format: jpeg, jpg, png.',
            'photo.max'       => 'Ukuran Foto Profil maksimal 2MB.',
        ];
    }
}
