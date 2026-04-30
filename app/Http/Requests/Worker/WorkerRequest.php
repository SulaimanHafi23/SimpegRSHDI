<?php

namespace App\Http\Requests\Worker;

use Carbon\Carbon;
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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'birth_date' => $this->normalizeDateInput($this->input('birth_date')),
            'hire_date' => $this->normalizeDateInput($this->input('hire_date')),
            'resign_date' => $this->normalizeDateInput($this->input('resign_date')),
        ]);
    }

    private function normalizeDateInput($value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return $value;
        }

        $value = trim($value);
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'm-d-Y'];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                // Try next supported format.
            }
        }

        return $value;
    }

    public function rules(): array
    {
        $workerId = $this->route('id');
        $minimumBirthDate = now()->subYears(17)->toDateString();

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
            'birth_date' => [
                'required',
                'date',
                'before:today',
                'before_or_equal:' . $minimumBirthDate,
            ],
            'address' => 'nullable|string|max:500',
            'religion' => ['required', Rule::in(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'])],
            'gender' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'department_id' => ['required', Rule::exists('departments', 'id')],
            'hire_date' => 'required|date|before_or_equal:today',
            'resign_date' => [
                'nullable',
                'date',
                'after_or_equal:hire_date',
                Rule::prohibitedIf(empty($workerId)),
            ],
            'employment_status' => ['required', Rule::in(['permanent', 'contract', 'internship'])],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'resigned'])],
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'nip.required'                   => 'NIP wajib diisi.',
            'nip.max'                        => 'NIP maksimal 50 karakter.',
            'nip.unique'                     => 'NIP sudah digunakan oleh pegawai lain.',
            'name.required'                  => 'Nama pegawai wajib diisi.',
            'name.max'                       => 'Nama maksimal 255 karakter.',
            'email.required'                 => 'Email wajib diisi.',
            'email.email'                    => 'Format email tidak valid.',
            'email.max'                      => 'Email maksimal 255 karakter.',
            'email.unique'                   => 'Email sudah digunakan oleh pegawai lain.',
            'phone_number.required'          => 'Nomor telepon wajib diisi.',
            'phone_number.max'               => 'Nomor telepon maksimal 20 karakter.',
            'phone_number.unique'            => 'Nomor telepon sudah digunakan oleh pegawai lain.',
            'birth_place.required'           => 'Tempat lahir wajib diisi.',
            'birth_place.max'                => 'Tempat lahir maksimal 100 karakter.',
            'birth_date.required'            => 'Tanggal Lahir wajib diisi.',
            'birth_date.date'                => 'Format Tanggal Lahir tidak valid.',
            'birth_date.before'              => 'Tanggal Lahir harus sebelum hari ini.',
            'birth_date.before_or_equal'     => 'Usia pegawai minimal 17 tahun.',
            'address.max'                    => 'Alamat maksimal 500 karakter.',
            'religion.required'              => 'Agama wajib dipilih.',
            'religion.in'                    => 'Agama yang dipilih tidak valid.',
            'gender.required'                => 'Jenis kelamin wajib dipilih.',
            'gender.in'                      => 'Jenis kelamin tidak valid.',
            'department_id.required'         => 'Departemen wajib dipilih.',
            'department_id.exists'           => 'Departemen yang dipilih tidak ditemukan.',
            'hire_date.required'             => 'Tanggal Bergabung wajib diisi.',
            'hire_date.date'                 => 'Format Tanggal Bergabung tidak valid.',
            'hire_date.before_or_equal'      => 'Tanggal Bergabung tidak boleh melebihi hari ini.',
            'resign_date.date'               => 'Format Tanggal Resign tidak valid.',
            'resign_date.after_or_equal'     => 'Tanggal Resign harus setelah atau sama dengan Tanggal Bergabung.',
            'resign_date.prohibited'         => 'Tanggal Resign hanya dapat diisi saat memperbarui data pegawai.',
            'employment_status.required'     => 'Status kepegawaian wajib dipilih.',
            'employment_status.in'           => 'Status kepegawaian tidak valid.',
            'status.in'                      => 'Status pegawai tidak valid.',
            'photo.image'                    => 'Foto harus berupa file gambar.',
            'photo.mimes'                    => 'Foto hanya mendukung format: jpeg, jpg, png.',
            'photo.max'                      => 'Ukuran foto maksimal 2MB.',
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
            'religion' => 'Agama',
            'gender' => 'Jenis Kelamin',
            'department_id' => 'Departemen',
            'hire_date' => 'Tanggal Bergabung',
            'resign_date' => 'Tanggal Resign',
            'employment_status' => 'Status Kepegawaian',
            'status' => 'Status',
            'photo' => 'Foto',
        ];
    }
}
