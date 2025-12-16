<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\WorkerShiftSchedule;

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
        $rules = [
            'worker_id' => ['required', Rule::exists('workers', 'id')],
            'shift_id' => ['required', Rule::exists('shifts', 'id')],
            'status' => ['nullable', Rule::in(array_keys(WorkerShiftSchedule::getStatuses()))],
            'notes' => 'nullable|string|max:1000',
        ];

        // Validation for default recurring schedule
        if ($this->input('is_default')) {
            $rules['day_of_week'] = ['required', Rule::in(array_keys(WorkerShiftSchedule::getDaysOfWeek()))];
            $rules['is_default'] = 'required|boolean';
        }

        // Validation for override/exception schedule
        if ($this->input('is_override')) {
            $rules['schedule_date'] = 'required|date';
            $rules['is_override'] = 'required|boolean';
            $rules['replaced_worker_id'] = ['nullable', Rule::exists('workers', 'id')];
        }

        // At least one type must be set
        if (!$this->input('is_default') && !$this->input('is_override')) {
            $rules['is_default'] = 'required_without:is_override|boolean';
            $rules['is_override'] = 'required_without:is_default|boolean';
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'worker_id' => 'Pekerja',
            'shift_id' => 'Shift',
            'day_of_week' => 'Hari',
            'is_default' => 'Jadwal Default',
            'schedule_date' => 'Tanggal Jadwal',
            'is_override' => 'Override Jadwal',
            'replaced_worker_id' => 'Pekerja Pengganti',
            'status' => 'Status',
            'notes' => 'Catatan',
        ];
    }

    public function messages(): array
    {
        return [
            'is_default.required_without' => 'Harus memilih jenis jadwal (Default atau Override)',
            'is_override.required_without' => 'Harus memilih jenis jadwal (Default atau Override)',
            'day_of_week.required' => ':attribute wajib diisi untuk jadwal default',
            'schedule_date.required' => ':attribute wajib diisi untuk jadwal override',
        ];
    }
}
