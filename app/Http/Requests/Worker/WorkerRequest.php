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
            'payroll_category' => ['nullable', Rule::in(['asn', 'pppk', 'pppk_paruh_waktu', 'non_asn', 'outsourced'])],
            'payroll_payment_type' => ['nullable', Rule::in(['individual', 'vendor_invoice']), 'required_if:payroll_category,outsourced'],
            'base_salary' => 'nullable|numeric|min:0',
            'rank' => 'nullable|string|max:100',
            'rank_level' => 'nullable|string|max:50',
            'weekly_work_hours' => 'nullable|integer|min:1|max:40|required_if:payroll_category,pppk_paruh_waktu',
            'outsourced_vendor' => 'nullable|string|max:150|required_if:payroll_category,outsourced',
            'outsourced_contract_start' => 'nullable|date',
            'outsourced_contract_end' => 'nullable|date|after_or_equal:outsourced_contract_start',
            'auto_sync_salary_components' => 'nullable|boolean',
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
            'payroll_category' => 'Kategori Penggajian',
            'payroll_payment_type' => 'Mode Pembayaran Payroll',
            'base_salary' => 'Gaji Pokok',
            'rank' => 'Pangkat',
            'rank_level' => 'Golongan',
            'weekly_work_hours' => 'Jam Kerja Mingguan',
            'outsourced_vendor' => 'Vendor Outsourcing',
            'outsourced_contract_start' => 'Mulai Kontrak Outsourcing',
            'outsourced_contract_end' => 'Akhir Kontrak Outsourcing',
            'status' => 'Status',
            'photo' => 'Foto',
        ];
    }
}
