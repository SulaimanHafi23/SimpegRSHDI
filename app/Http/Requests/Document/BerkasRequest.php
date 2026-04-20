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
        $fileRule = $this->isMethod('POST') ? 'required' : 'nullable';

        return [
            'worker_id' => ['required', 'uuid', Rule::exists('workers', 'id')],
            'document_type_id' => ['nullable', 'uuid', Rule::exists('document_types', 'id'), 'required_without:department_document_type_id'],
            // Legacy compatibility: old UI may still submit file_requirement_id.
            'file_requirement_id' => ['nullable', 'uuid', Rule::exists('department_document_type', 'id')],
            'department_document_type_id' => ['nullable', 'uuid', Rule::exists('department_document_type', 'id'), 'required_without:document_type_id'],
            'file' => $fileRule . '|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB
            'notes' => 'nullable|string|max:1000',
            'expired_date' => 'nullable|date|after:today',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->filled('department_document_type_id') && $this->filled('file_requirement_id')) {
            $this->merge([
                'department_document_type_id' => $this->input('file_requirement_id'),
            ]);
        }
    }

    public function attributes(): array
    {
        return [
            'worker_id' => 'Pekerja',
            'document_type_id' => 'Jenis Dokumen',
            'file_requirement_id' => 'Jenis Dokumen (Legacy)',
            'department_document_type_id' => 'Jenis Dokumen Departemen',
            'file' => 'File',
            'notes' => 'Catatan',
            'expired_date' => 'Tanggal Kadaluarsa',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => ':attribute harus diupload',
            'file.mimes' => ':attribute harus berformat: pdf, doc, docx, jpg, jpeg, png',
            'file.max' => ':attribute maksimal 10MB',
        ];
    }
}
