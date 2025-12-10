<?php

namespace App\Http\Requests\Overtime;

use Illuminate\Foundation\Http\FormRequest;

class OvertimeRequest extends FormRequest
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
            'date' => [
                'required',
                'date',
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
            ],
            'reason' => [
                'required',
                'string',
                'max:1000',
            ],
            'notes' => [
                'nullable',
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
            'date.required' => 'Tanggal harus diisi.',
            'start_time.required' => 'Jam mulai harus diisi.',
            'start_time.date_format' => 'Format jam mulai tidak valid (HH:MM).',
            'end_time.required' => 'Jam selesai harus diisi.',
            'end_time.date_format' => 'Format jam selesai tidak valid (HH:MM).',
            'reason.required' => 'Alasan lembur harus diisi.',
            'attachment.max' => 'Ukuran file maksimal 5MB.',
            'attachment.mimes' => 'Format file harus: pdf, jpg, jpeg, png, doc, atau docx.',
        ];
    }
}
