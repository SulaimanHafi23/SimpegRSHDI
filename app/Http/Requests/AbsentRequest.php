<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AbsentRequest extends FormRequest
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
        $isCheckOut = $this->has('check_out');

        return [
            'worker_id' => [
                'required',
                'uuid',
                'exists:workers,id',
            ],
            'location_id' => [
                'required',
                'uuid',
                'exists:locations,id',
            ],
            'date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'check_in' => [
                'required',
                'date_format:H:i:s',
            ],
            'check_out' => [
                'nullable',
                'date_format:H:i:s',
            ],
            'check_in_photo' => [
                'nullable',
                'image',
                'max:2048', // 2MB
                'mimes:jpg,jpeg,png',
            ],
            'check_out_photo' => [
                $isCheckOut ? 'nullable' : 'nullable',
                'image',
                'max:2048',
                'mimes:jpg,jpeg,png',
            ],
            'check_in_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'check_in_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'check_out_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'check_out_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => [
                'required',
                Rule::in(['present', 'late', 'absent', 'leave', 'sick', 'permission']),
            ],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'worker_id.required' => 'Pegawai harus dipilih.',
            'worker_id.exists' => 'Pegawai tidak ditemukan.',
            'location_id.required' => 'Lokasi harus dipilih.',
            'location_id.exists' => 'Lokasi tidak ditemukan.',
            'date.required' => 'Tanggal harus diisi.',
            'date.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
            'check_in.required' => 'Waktu check-in harus diisi.',
            'check_in.date_format' => 'Format waktu check-in harus HH:MM:SS.',
            'check_out.date_format' => 'Format waktu check-out harus HH:MM:SS.',
            'check_in_photo.image' => 'File foto check-in harus berupa gambar.',
            'check_in_photo.max' => 'Ukuran foto check-in maksimal 2MB.',
            'check_out_photo.image' => 'File foto check-out harus berupa gambar.',
            'check_out_photo.max' => 'Ukuran foto check-out maksimal 2MB.',
            'status.in' => 'Status absensi tidak valid.',
        ];
    }
}
