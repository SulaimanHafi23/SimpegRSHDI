<?php

namespace App\Http\Requests\Overtime;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OvertimeRequest extends FormRequest
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
            'worker_id' => ['required', Rule::exists('workers', 'id')],
            'date' => 'required|date',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s|after:start_time',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function attributes(): array
    {
        return [
            'worker_id' => 'Pekerja',
            'date' => 'Tanggal',
            'start_time' => 'Waktu Mulai',
            'end_time' => 'Waktu Selesai',
            'reason' => 'Alasan',
            'notes' => 'Catatan',
        ];
    }

    public function messages(): array
    {
        return [
            'end_time.after' => ':attribute harus setelah waktu mulai',
        ];
    }
}
