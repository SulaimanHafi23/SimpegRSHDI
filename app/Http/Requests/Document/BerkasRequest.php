<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BerkasRequest extends FormRequest
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
        $isUpdate = $this->route('berkas') !== null;

        return [
            'worker_id' => [
                'required',
                'uuid',
                'exists:workers,id',
            ],
            'file_requirement_id' => [
                'required',
                'uuid',
                'exists:file_requirments,id',
            ],
            'file' => [
                $isUpdate ? 'nullable' : 'required',
                'file',
                'max:5120', // 5MB
                'mimes:pdf,jpg,jpeg,png,doc,docx',
            ],
            'notes' => ['nullable', 'string', 'max:500'],
            'status' => [
                'nullable',
                Rule::in(['pending', 'approved', 'rejected']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'worker_id.required' => 'Pegawai harus dipilih.',
            'file_requirement_id.required' => 'Jenis dokumen harus dipilih.',
            'file.required' => 'File dokumen harus diupload.',
            'file.max' => 'Ukuran file maksimal 5MB.',
            'file.mimes' => 'Format file harus: pdf, jpg, jpeg, png, doc, atau docx.',
        ];
    }
}
