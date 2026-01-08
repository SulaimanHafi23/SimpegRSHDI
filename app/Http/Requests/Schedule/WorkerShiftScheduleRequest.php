<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkerShiftScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'worker_id' => ['nullable', Rule::exists('workers', 'id')],
            'worker_ids' => ['nullable', 'array'],
            'worker_ids.*' => [Rule::exists('workers', 'id')],
            'shift_id' => ['required', Rule::exists('shifts', 'id')],
            'pattern_type' => ['required', Rule::in(['fixed','rotating','custom'])],
            'rotating_days' => ['nullable','array'],
            'rotating_days.*' => ['nullable','string', Rule::exists('shifts','id')],
            'custom_working_days' => ['nullable','array'],
            'custom_working_days.*' => ['integer', Rule::in([1,2,3,4,5,6,7])],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ];

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $workerIds = $this->input('worker_ids');
            $workerId = $this->input('worker_id');
            
            // Check if at least one worker is selected
            if (empty($workerIds) && empty($workerId)) {
                $validator->errors()->add('worker_ids', 'Minimal satu pegawai harus dipilih.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'worker_id' => 'Pegawai',
            'worker_ids' => 'Pegawai',
            'worker_ids.*' => 'Pegawai',
            'shift_id' => 'Shift',
            'start_date' => 'Tanggal Mulai',
            'end_date' => 'Tanggal Selesai',
            'is_active' => 'Status Aktif',
            'pattern_type' => 'Tipe Pola',
            'rotating_days' => 'Rotasi Hari',
            'custom_working_days' => 'Hari Kerja Khusus',
        ];
    }

    public function messages(): array
    {
        return [
            'worker_id.required' => ':attribute wajib dipilih',
            'worker_id.exists' => ':attribute tidak valid',
            'worker_ids.*.exists' => ':attribute tidak valid',
            'shift_id.required' => ':attribute wajib dipilih',
            'shift_id.exists' => ':attribute tidak valid',
            'start_date.required' => ':attribute wajib diisi',
            'start_date.date' => ':attribute harus berupa tanggal yang valid',
            'end_date.date' => ':attribute harus berupa tanggal yang valid',
            'end_date.after_or_equal' => ':attribute harus sama dengan atau setelah tanggal mulai',
        ];
    }
}
