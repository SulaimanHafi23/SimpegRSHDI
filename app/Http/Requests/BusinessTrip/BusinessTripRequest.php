<?php

namespace App\Http\Requests\BusinessTrip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        // Minimal 1 hari sebelum keberangkatan (tomorrow)
        $minStartDate = now()->addDay()->format('Y-m-d');
        
        return [
            'destination' => 'required|string|max:255',
            'purpose' => 'required|string|max:1000',
            'start_date' => 'required|date|after_or_equal:' . $minStartDate,
            'end_date' => 'required|date|after_or_equal:start_date',
            'transportation' => 'required|string|max:255',
            'accommodation' => 'nullable|string|max:255',
            'estimated_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function attributes(): array
    {
        return [
            'worker_id' => 'Pekerja',
            'destination' => 'Tujuan',
            'purpose' => 'Tujuan Perjalanan',
            'start_date' => 'Tanggal Mulai',
            'end_date' => 'Tanggal Selesai',
            'transportation' => 'Transportasi',
            'accommodation' => 'Akomodasi',
            'estimated_cost' => 'Estimasi Biaya',
            'notes' => 'Catatan',
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.after_or_equal' => 'Perjalanan dinas harus diajukan minimal 1 hari sebelum keberangkatan. Tanggal keberangkatan paling cepat besok (' . now()->addDay()->format('d M Y') . ').',
        ];
    }
}
