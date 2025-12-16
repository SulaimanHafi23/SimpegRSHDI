<?php
// filepath: app/Http/Requests/BusinessTrip/BusinessTripReportRequest.php

namespace App\Http\Requests\BusinessTrip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\BusinessTripReport;

class BusinessTripReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_trip_id' => ['required', Rule::exists('business_trips', 'id')],
            'report_title' => 'required|string|max:255',
            'report_content' => 'required|string',
            'status' => ['nullable', Rule::in(array_keys(BusinessTripReport::getStatuses()))],
            'review_notes' => 'nullable|string|max:1000',
        ];
    }

    public function attributes(): array
    {
        return [
            'business_trip_id' => 'Perjalanan Dinas',
            'report_title' => 'Judul Laporan',
            'report_content' => 'Isi Laporan',
            'status' => 'Status',
            'review_notes' => 'Catatan Review',
        ];
    }
}