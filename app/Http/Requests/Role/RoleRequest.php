<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('role.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get role ID from route parameter (for update)
        $roleId = $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($roleId),
            ],
            'display_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'permissions' => [
                'nullable',
                'array',
            ],
            'permissions.*' => [
                'integer',
                'exists:permissions,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'Nama role wajib diisi.',
            'name.max'             => 'Nama role maksimal 255 karakter.',
            'name.unique'          => 'Nama role sudah digunakan.',
            'display_name.max'     => 'Nama tampilan role maksimal 255 karakter.',
            'permissions.array'    => 'Data permission tidak valid.',
            'permissions.*.integer' => 'ID permission harus berupa angka.',
            'permissions.*.exists'  => 'Permission yang dipilih tidak valid.',
        ];
    }
}
