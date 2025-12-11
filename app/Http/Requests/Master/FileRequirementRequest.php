<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FileRequirementRequest extends FormRequest
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
        $fileRequirementId = $this->route('file_requirement');

        return [
            'position_id' => [
                'required',
                'uuid',
                'exists:positions,id',
            ],
            'document_type_id' => [
                'required',
                'uuid',
                'exists:document_types,id',
                Rule::unique('file_requirments', 'document_type_id')
                    ->where('position_id', $this->position_id)
                    ->ignore($fileRequirementId),
            ],
            'is_mandatory' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'position_id.required' => 'Jabatan harus dipilih.',
            'position_id.exists' => 'Jabatan tidak ditemukan.',
            'document_type_id.required' => 'Jenis dokumen harus dipilih.',
            'document_type_id.exists' => 'Jenis dokumen tidak ditemukan.',
            'document_type_id.unique' => 'Persyaratan dokumen ini sudah ada untuk jabatan yang dipilih.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_mandatory' => $this->has('is_mandatory') ?
                filter_var($this->is_mandatory, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false : 
                true, // default is mandatory
        ]);
    }
}
