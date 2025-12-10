<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PositionRequest extends FormRequest
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
        $positionId = $this->route('position');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('positions', 'name')->ignore($positionId),
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama jabatan harus diisi.',
            'name.unique' => 'Jabatan ini sudah ada.',
            'name.max' => 'Nama jabatan maksimal 100 karakter.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
        ];
    }
}
