<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\LeaveRequest;

class LeaveRequestRequest extends FormRequest
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
                Rule::exists('workers', 'id'),
            ],
            'leave_type_id' => [
                'required',
                Rule::exists('leave_types', 'id'),
            ],
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function attributes(): array
    {
        return [
            'worker_id' => 'Pekerja',
            'leave_type_id' => 'Jenis Cuti',
            'start_date' => 'Tanggal Mulai',
            'end_date' => 'Tanggal Selesai',
            'reason' => 'Alasan',
            'attachment' => 'Lampiran',
            'notes' => 'Catatan',
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.after_or_equal' => ':attribute tidak boleh kurang dari hari ini',
            'end_date.after_or_equal' => ':attribute harus setelah atau sama dengan tanggal mulai',
        ];
    }
}
