<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShiftRequest extends FormRequest
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
        $shiftId = $this->route('shift');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('shifts', 'name')->ignore($shiftId),
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama shift harus diisi.',
            'name.unique' => 'Shift ini sudah ada.',
            'name.max' => 'Nama shift maksimal 100 karakter.',
            'start_time.required' => 'Jam mulai harus diisi.',
            'start_time.date_format' => 'Format jam mulai harus HH:MM (contoh: 07:00).',
            'end_time.required' => 'Jam selesai harus diisi.',
            'end_time.date_format' => 'Format jam selesai harus HH:MM (contoh: 15:00).',
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
            'start_time' => 'jam mulai',
            'end_time' => 'jam selesai',
            'is_active' => 'status aktif',
        ];
    }
}
