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

    public function messages(): array
    {
        return [
            'worker_id.required'   => 'Pekerja wajib dipilih.',
            'worker_id.exists'     => 'Pekerja yang dipilih tidak ditemukan.',
            'date.required'        => 'Tanggal wajib diisi.',
            'date.date'            => 'Format tanggal tidak valid.',
            'check_in.required'    => 'Jam Masuk wajib diisi.',
            'check_in.date'        => 'Format Jam Masuk tidak valid.',
            'check_out.date'       => 'Format Jam Keluar tidak valid.',
            'check_out.after'      => 'Jam Keluar harus setelah Jam Masuk.',
            'status.required'      => 'Status kehadiran wajib dipilih.',
            'status.in'            => 'Status kehadiran tidak valid.',
            'notes.max'            => 'Catatan maksimal 500 karakter.',
            'photo_in.image'       => 'Foto Masuk harus berupa file gambar.',
            'photo_in.mimes'       => 'Foto Masuk hanya mendukung format: jpeg, jpg, png.',
            'photo_in.max'         => 'Ukuran Foto Masuk maksimal 2MB.',
            'photo_out.image'      => 'Foto Keluar harus berupa file gambar.',
            'photo_out.mimes'      => 'Foto Keluar hanya mendukung format: jpeg, jpg, png.',
            'photo_out.max'        => 'Ukuran Foto Keluar maksimal 2MB.',
        ];
    }
}
