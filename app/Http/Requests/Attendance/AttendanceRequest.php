<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get available attendance statuses
     */
    public static function getStatuses(): array
    {
        return [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            'leave' => 'Cuti',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'worker_id' => ['required', Rule::exists('workers', 'id')],
            'date' => 'required|date',
            'check_in' => 'required|date',
            'check_out' => 'nullable|date|after:check_in',
            'status' => ['required', Rule::in(array_keys(AttendanceRequest::getStatuses()))],
            'notes' => 'nullable|string|max:500',
            'photo_in' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'photo_out' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ];
    }

    public function attributes(): array
    {
        return [
            'worker_id' => 'Pekerja',
            'date' => 'Tanggal',
            'check_in' => 'Jam Masuk',
            'check_out' => 'Jam Keluar',
            'status' => 'Status',
            'notes' => 'Catatan',
            'photo_in' => 'Foto Masuk',
            'photo_out' => 'Foto Keluar',
        ];
    }
}
