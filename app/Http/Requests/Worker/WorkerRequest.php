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
            'birth_date.before' => 'Tanggal Lahir harus sebelum hari ini.',
            'birth_date.before_or_equal' => 'Usia pegawai minimal 17 tahun.',
            'hire_date.before_or_equal' => 'Tanggal Masuk tidak boleh melebihi hari ini.',
            'resign_date.prohibited' => 'Tanggal Resign hanya diisi saat update data pegawai.',
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
