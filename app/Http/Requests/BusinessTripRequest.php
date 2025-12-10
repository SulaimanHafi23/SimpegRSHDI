<?php

namespace App\Http\Requests\BusinessTrip;

use Illuminate\Foundation\Http\FormRequest;

class BusinessTripRequest extends FormRequest
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
        return [
            'worker_id' => [
                'required',
                'uuid',
                'exists:workers,id',
            ],
            'destination' => [
                'required',
                'string',
                'max:255',
            ],
            'purpose' => [
                'required',
                'string',
                'max:1000',
            ],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],
            'transport_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'accommodation_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'meal_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'other_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'worker_id.required' => 'Pegawai harus dipilih.',
            'destination.required' => 'Tujuan perjalanan dinas harus diisi.',
            'purpose.required' => 'Tujuan/keperluan harus diisi.',
            'start_date.required' => 'Tanggal mulai harus diisi.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh di masa lalu.',
            'end_date.required' => 'Tanggal selesai harus diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'transport_cost.numeric' => 'Biaya transportasi harus berupa angka.',
            'transport_cost.min' => 'Biaya transportasi tidak boleh negatif.',
        ];
    }
}
