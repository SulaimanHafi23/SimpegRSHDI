<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkerShiftScheduleRequest extends FormRequest
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
        return [
            'worker_id' => [
                'required',
                'uuid',
                'exists:workers,id',
            ],
            'shift_id' => [
                'required',
                'uuid',
                'exists:shifts,id',
            ],
            'shift_pattern_id' => [
                'required',
                'uuid',
                'exists:shift_patterns,id',
            ],
            'schedule_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'replaced_worker_id' => [
                'nullable',
                'uuid',
                'exists:workers,id',
                'different:worker_id',
            ],
            'notes' => ['nullable', 'string', 'max:500'],
            'status' => [
                'required',
                Rule::in(['scheduled', 'completed', 'cancelled', 'swapped']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'worker_id.required' => 'Pegawai harus dipilih.',
            'worker_id.exists' => 'Pegawai tidak ditemukan.',
            'shift_id.required' => 'Shift harus dipilih.',
            'shift_id.exists' => 'Shift tidak ditemukan.',
            'shift_pattern_id.required' => 'Pola shift harus dipilih.',
            'shift_pattern_id.exists' => 'Pola shift tidak ditemukan.',
            'schedule_date.required' => 'Tanggal jadwal harus diisi.',
            'schedule_date.after_or_equal' => 'Tanggal jadwal tidak boleh di masa lalu.',
            'replaced_worker_id.different' => 'Pegawai pengganti tidak boleh sama dengan pegawai yang diganti.',
            'status.in' => 'Status tidak valid.',
        ];
    }
}
