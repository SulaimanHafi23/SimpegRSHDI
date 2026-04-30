<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\LeaveRequest;

class LeaveRequestRequest extends FormRequest
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
            'worker_id' => [
                'required',
                Rule::exists('workers', 'id'),
            ],
            'leave_type_id' => [
                'required',
                Rule::exists('leave_types', 'id'),
            ],
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function attributes(): array
    {
        return [
            'worker_id' => 'Pekerja',
            'leave_type_id' => 'Jenis Cuti',
            'start_date' => 'Tanggal Mulai',
            'end_date' => 'Tanggal Selesai',
            'reason' => 'Alasan',
            'attachment' => 'Lampiran',
            'notes' => 'Catatan',
        ];
    }

    public function messages(): array
    {
        return [
            'worker_id.required'          => 'Pekerja wajib dipilih.',
            'worker_id.exists'            => 'Pekerja yang dipilih tidak ditemukan.',
            'leave_type_id.required'      => 'Jenis cuti wajib dipilih.',
            'leave_type_id.exists'        => 'Jenis cuti yang dipilih tidak valid.',
            'start_date.required'         => 'Tanggal Mulai wajib diisi.',
            'start_date.date'             => 'Format Tanggal Mulai tidak valid.',
            'start_date.after_or_equal'   => 'Tanggal Mulai tidak boleh kurang dari hari ini.',
            'end_date.required'           => 'Tanggal Selesai wajib diisi.',
            'end_date.date'               => 'Format Tanggal Selesai tidak valid.',
            'end_date.after_or_equal'     => 'Tanggal Selesai harus setelah atau sama dengan Tanggal Mulai.',
            'reason.required'             => 'Alasan cuti wajib diisi.',
            'reason.max'                  => 'Alasan maksimal 1000 karakter.',
            'attachment.file'             => 'Lampiran harus berupa file.',
            'attachment.mimes'            => 'Lampiran hanya mendukung format: pdf, jpg, jpeg, png.',
            'attachment.max'              => 'Ukuran lampiran maksimal 5MB.',
            'notes.max'                   => 'Catatan maksimal 500 karakter.',
        ];
    }
}
