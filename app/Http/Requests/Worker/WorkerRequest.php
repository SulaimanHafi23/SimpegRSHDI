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
        $workerId = $this->route('worker');

        return [
            'nik' => [
                'required',
                'string',
                'max:20',
                Rule::unique('workers', 'nik')->ignore($workerId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('workers', 'email')->ignore($workerId),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
            ],
            'gender_id' => ['required', 'uuid', 'exists:genders,id'],
            'religion_id' => ['required', 'uuid', 'exists:religions,id'],
            'position_id' => ['required', 'uuid', 'exists:positions,id'],
            'place_of_birth' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'address' => ['required', 'string', 'max:500'],
            'hire_date' => ['required', 'date', 'before_or_equal:today'],
            'status' => [
                'required',
                Rule::in(['permanent', 'contract', 'intern', 'outsourcing']),
            ],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.required' => 'NIK harus diisi.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'nik.max' => 'NIK maksimal 20 karakter.',
            'name.required' => 'Nama lengkap harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.required' => 'Nomor telepon harus diisi.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'gender_id.required' => 'Jenis kelamin harus dipilih.',
            'religion_id.required' => 'Agama harus dipilih.',
            'position_id.required' => 'Jabatan harus dipilih.',
            'place_of_birth.required' => 'Tempat lahir harus diisi.',
            'date_of_birth.required' => 'Tanggal lahir harus diisi.',
            'date_of_birth.before' => 'Tanggal lahir harus sebelum hari ini.',
            'address.required' => 'Alamat harus diisi.',
            'hire_date.required' => 'Tanggal bergabung harus diisi.',
            'hire_date.before_or_equal' => 'Tanggal bergabung tidak boleh di masa depan.',
            'status.required' => 'Status pegawai harus dipilih.',
            'status.in' => 'Status pegawai tidak valid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->has('is_active') ? 
                filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false : 
                true,
        ]);
    }
}
