<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShiftPatternRequest extends FormRequest
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
        $shiftPatternId = $this->route('shift_pattern');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('shift_patterns', 'name')->ignore($shiftPatternId),
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['daily', 'weekdays', 'weekends', 'custom']),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama pola shift harus diisi.',
            'name.unique' => 'Pola shift ini sudah ada.',
            'name.max' => 'Nama pola shift maksimal 100 karakter.',
            'type.required' => 'Tipe pola shift harus dipilih.',
            'type.in' => 'Tipe pola shift tidak valid. Pilih: daily, weekdays, weekends, atau custom.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->has('is_active') ? 
                filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false : 
                false,
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'type' => 'tipe pola shift',
            'is_active' => 'status aktif',
        ];
    }
}
