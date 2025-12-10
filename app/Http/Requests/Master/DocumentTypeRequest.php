<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentTypeRequest extends FormRequest
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
        $documentTypeId = $this->route('document_type');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('document_types', 'name')->ignore($documentTypeId),
            ],
            'file_format' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9,\s]+$/', // Allow alphanumeric, comma, and spaces
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama jenis dokumen harus diisi.',
            'name.unique' => 'Jenis dokumen ini sudah ada.',
            'name.max' => 'Nama jenis dokumen maksimal 100 karakter.',
            'file_format.required' => 'Format file harus diisi.',
            'file_format.regex' => 'Format file hanya boleh berisi huruf, angka, dan koma (contoh: pdf,jpg,png).',
            'file_format.max' => 'Format file maksimal 50 karakter.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'file_format' => 'format file',
        ];
    }
}
