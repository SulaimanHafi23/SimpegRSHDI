<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenderRequest extends FormRequest
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
        $genderId = $this->route('gender');

        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('genders', 'name')->ignore($genderId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama jenis kelamin harus diisi.',
            'name.unique' => 'Jenis kelamin ini sudah ada.',
            'name.max' => 'Nama jenis kelamin maksimal 50 karakter.',
        ];
    }
}
