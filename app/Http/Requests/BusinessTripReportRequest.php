<?php

namespace App\Http\Requests\BusinessTrip;

use Illuminate\Foundation\Http\FormRequest;

class BusinessTripReportRequest extends FormRequest
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
            'business_trip_id' => [
                'required',
                'uuid',
                'exists:business_trips,id',
            ],
            'report_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'activities' => [
                'required',
                'string',
            ],
            'results' => [
                'required',
                'string',
            ],
            'attachment' => [
                'nullable',
                'file',
                'max:10240', // 10MB
                'mimes:pdf,jpg,jpeg,png,doc,docx',
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
            'business_trip_id.required' => 'ID Perjalanan dinas harus ada.',
            'report_date.required' => 'Tanggal laporan harus diisi.',
            'report_date.before_or_equal' => 'Tanggal laporan tidak boleh di masa depan.',
            'activities.required' => 'Kegiatan yang dilakukan harus diisi.',
            'results.required' => 'Hasil perjalanan dinas harus diisi.',
            'attachment.max' => 'Ukuran file maksimal 10MB.',
        ];
    }
}
