<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReligionRequest extends FormRequest
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
        $religionId = $this->route('religion');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('religions', 'name')->ignore($religionId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama agama harus diisi.',
            'name.unique' => 'Agama ini sudah ada.',
            'name.max' => 'Nama agama maksimal 100 karakter.',
        ];
    }
}
