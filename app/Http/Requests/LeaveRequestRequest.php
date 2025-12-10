<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
                'uuid',
                'exists:workers,id',
            ],
            'leave_type' => [
                'required',
                Rule::in(['annual', 'sick', 'permission', 'maternity', 'marriage', 'bereavement', 'unpaid']),
            ],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],
            'reason' => [
                'required',
                'string',
                'max:1000',
            ],
            'attachment' => [
                'nullable',
                'file',
                'max:5120', // 5MB
                'mimes:pdf,jpg,jpeg,png,doc,docx',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'worker_id.required' => 'Pegawai harus dipilih.',
            'leave_type.required' => 'Jenis cuti harus dipilih.',
            'leave_type.in' => 'Jenis cuti tidak valid.',
            'start_date.required' => 'Tanggal mulai harus diisi.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh di masa lalu.',
            'end_date.required' => 'Tanggal selesai harus diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'reason.required' => 'Alasan cuti harus diisi.',
            'reason.max' => 'Alasan cuti maksimal 1000 karakter.',
            'attachment.max' => 'Ukuran file maksimal 5MB.',
            'attachment.mimes' => 'Format file harus: pdf, jpg, jpeg, png, doc, atau docx.',
        ];
    }

    public function attributes(): array
    {
        return [
            'worker_id' => 'Pegawai',
            'leave_type' => 'Jenis Cuti',
            'start_date' => 'Tanggal Mulai',
            'end_date' => 'Tanggal Selesai',
            'reason' => 'Alasan',
            'attachment' => 'Lampiran',
        ];
    }
}
