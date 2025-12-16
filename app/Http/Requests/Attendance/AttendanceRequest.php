<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Absent;

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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'worker_id' => ['required', Rule::exists('workers', 'id')],
            'date' => 'required|date',
            'check_in' => 'required|date_format:Y-m-d H:i:s',
            'check_out' => 'nullable|date_format:Y-m-d H:i:s|after:check_in',
            'status' => ['required', Rule::in(array_keys(AttendanceRequest::getStatuses()))],
            'notes' => 'nullable|string|max:500',
            'latitude_in' => 'nullable|numeric|between:-90,90',
            'longitude_in' => 'nullable|numeric|between:-180,180',
            'latitude_out' => 'nullable|numeric|between:-90,90',
            'longitude_out' => 'nullable|numeric|between:-180,180',
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
            'latitude_in' => 'Latitude Masuk',
            'longitude_in' => 'Longitude Masuk',
            'latitude_out' => 'Latitude Keluar',
            'longitude_out' => 'Longitude Keluar',
            'photo_in' => 'Foto Masuk',
            'photo_out' => 'Foto Keluar',
        ];
    }
}
