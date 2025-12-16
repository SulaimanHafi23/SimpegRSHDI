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
        return [
            'worker_id' => ['required', Rule::exists('workers', 'id')],
            'file_requirement_id' => ['required', Rule::exists('file_requirments', 'id')],
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB
            'notes' => 'nullable|string|max:1000',
            'expired_date' => 'nullable|date|after:today',
        ];
    }

    public function attributes(): array
    {
        return [
            'worker_id' => 'Pekerja',
            'file_requirement_id' => 'Jenis Dokumen',
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
