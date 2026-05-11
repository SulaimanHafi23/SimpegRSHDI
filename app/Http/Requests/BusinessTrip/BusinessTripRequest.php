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
        $minStartDate = now()->addDay()->format('Y-m-d');

        return [
            'destination' => 'required|string|max:255',
            'purpose' => 'required|string|max:1000',
            'start_date' => 'required|date|after_or_equal:' . $minStartDate,
            'end_date' => 'required|date|after_or_equal:start_date',
            'trip_duration_type' => 'required|in:full_day,half_day',
            'half_day_session' => 'nullable|required_if:trip_duration_type,half_day|in:pagi,siang',
            'transportation' => 'required|string|max:255',
            'accommodation' => 'nullable|string|max:255',
            'estimated_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'supporting_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    protected function prepareForValidation(): void
    {
        $tripDurationType = $this->input('trip_duration_type', 'full_day');

        $payload = [
            'trip_duration_type' => $tripDurationType,
        ];

        if ($tripDurationType === 'half_day' && $this->filled('start_date')) {
            $payload['end_date'] = $this->input('start_date');
        }

        $this->merge($payload);
    }

    public function attributes(): array
    {
        return [
            'worker_id' => 'Pekerja',
            'destination' => 'Tujuan',
            'purpose' => 'Tujuan Perjalanan',
            'start_date' => 'Tanggal Mulai',
            'end_date' => 'Tanggal Selesai',
            'trip_duration_type' => 'Tipe Durasi',
            'half_day_session' => 'Sesi Setengah Hari',
            'transportation' => 'Transportasi',
            'accommodation' => 'Akomodasi',
            'estimated_cost' => 'Estimasi Biaya',
            'notes' => 'Catatan',
            'supporting_document' => 'Dokumen Surat Tugas/Disposisi',
        ];
    }

    public function messages(): array
    {
        return [
            'destination.required'            => 'Tujuan perjalanan wajib diisi.',
            'destination.max'                 => 'Tujuan perjalanan maksimal 255 karakter.',
            'purpose.required'                => 'Tujuan/keperluan perjalanan wajib diisi.',
            'purpose.max'                     => 'Tujuan/keperluan maksimal 1000 karakter.',
            'start_date.required'             => 'Tanggal keberangkatan wajib diisi.',
            'start_date.date'                 => 'Format tanggal keberangkatan tidak valid.',
            'start_date.after_or_equal'       => 'Perjalanan dinas harus diajukan minimal 1 hari sebelum keberangkatan. Tanggal keberangkatan paling cepat besok (' . now()->addDay()->format('d M Y') . ').',
            'end_date.required'               => 'Tanggal kembali wajib diisi.',
            'end_date.date'                   => 'Format tanggal kembali tidak valid.',
            'end_date.after_or_equal'         => 'Tanggal kembali harus setelah atau sama dengan tanggal keberangkatan.',
            'trip_duration_type.required'     => 'Tipe durasi perjalanan wajib dipilih.',
            'trip_duration_type.in'           => 'Tipe durasi tidak valid. Pilih Satu Hari Penuh atau Setengah Hari.',
            'half_day_session.required_if'    => 'Sesi setengah hari wajib dipilih untuk tipe setengah hari.',
            'half_day_session.in'             => 'Sesi setengah hari tidak valid.',
            'transportation.required'         => 'Transportasi wajib diisi.',
            'transportation.max'              => 'Transportasi maksimal 255 karakter.',
            'accommodation.max'               => 'Akomodasi maksimal 255 karakter.',
            'estimated_cost.required'         => 'Estimasi biaya wajib diisi.',
            'estimated_cost.numeric'          => 'Estimasi biaya harus berupa angka.',
            'estimated_cost.min'              => 'Estimasi biaya tidak boleh negatif.',
            'notes.max'                       => 'Catatan maksimal 500 karakter.',

            'supporting_document.file'        => 'Dokumen pendukung harus berupa file.',
            'supporting_document.mimes'       => 'Dokumen pendukung hanya mendukung format: pdf, jpg, jpeg, png.',
            'supporting_document.max'         => 'Ukuran dokumen pendukung maksimal 5MB.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('trip_duration_type') === 'half_day' && $this->input('start_date') !== $this->input('end_date')) {
                $validator->errors()->add('end_date', 'Perjalanan dinas setengah hari harus selesai pada tanggal yang sama dengan tanggal keberangkatan.');
            }
        });
    }
}
