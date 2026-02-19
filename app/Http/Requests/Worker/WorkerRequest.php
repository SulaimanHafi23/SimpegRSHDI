<?php

namespace App\Http\Requests\Worker;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkerRequest extends FormRequest
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $workerId = $this->route('id');

        return [
            'nip' => [
                'required',
                'string',
                'max:50',
                Rule::unique('workers', 'nip')->ignore($workerId),
            ],
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('workers', 'email')->ignore($workerId),
            ],
            'phone_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('workers', 'phone_number')->ignore($workerId),
            ],
            'birth_place' => 'required|string|max:100',
            'birth_date' => 'required|date|before:today',
            'address' => 'nullable|string|max:500',
            'religion_id' => ['required', Rule::exists('religions', 'id')],
            'gender_id' => ['required', Rule::exists('genders', 'id')],
            'department_id' => ['required', Rule::exists('departments', 'id')],
            'hire_date' => 'required|date',
            'resign_date' => 'nullable|date|after_or_equal:hire_date',
            'employment_status' => ['required', Rule::in(['permanent', 'contract', 'internship'])],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'resigned'])],
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'birth_date.before' => 'Tanggal Lahir harus sebelum hari ini.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nip' => 'NIP',
            'name' => 'Nama',
            'email' => 'Email',
            'phone_number' => 'No. Telepon',
            'birth_place' => 'Tempat Lahir',
            'birth_date' => 'Tanggal Lahir',
            'address' => 'Alamat',
            'religion_id' => 'Agama',
            'gender_id' => 'Jenis Kelamin',
            'department_id' => 'Departemen',
            'hire_date' => 'Tanggal Bergabung',
            'resign_date' => 'Tanggal Resign',
            'employment_status' => 'Status Kepegawaian',
            'status' => 'Status',
            'photo' => 'Foto',
        ];
    }
}
