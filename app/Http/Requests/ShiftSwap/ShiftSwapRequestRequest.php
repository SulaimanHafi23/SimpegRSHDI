<?php

namespace App\Http\Requests\ShiftSwap;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShiftSwapRequestRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function prepareForValidation()
    {
        // Clean up empty fields to null so they work properly with nullable rules
        $cleanedData = [
            'target_worker_id' => $this->input('target_worker_id') ?: null,
            'target_shift_id' => $this->input('target_shift_id') ?: null,
            'reason' => $this->input('reason') ?: null,
            'expires_at' => $this->input('expires_at') ?: null,
        ];

        // Handle different swap types
        $swapType = $this->input('swap_type');
        
        if ($swapType === 'single_date') {
            $cleanedData['swap_date'] = $this->input('swap_date') ?: null;
            $cleanedData['swap_start_date'] = null;
            $cleanedData['swap_end_date'] = null;
            $cleanedData['swap_dates'] = null;
        } elseif ($swapType === 'date_range') {
            $cleanedData['swap_start_date'] = $this->input('swap_start_date') ?: null;
            $cleanedData['swap_end_date'] = $this->input('swap_end_date') ?: null;
            $cleanedData['swap_date'] = null;
            $cleanedData['swap_dates'] = null;
        } elseif ($swapType === 'recurring') {
            // Clean up swap_dates array to remove empty values
            if ($this->input('swap_dates')) {
                $swapDates = array_filter($this->input('swap_dates'), function($date) {
                    return !empty($date);
                });
                $cleanedData['swap_dates'] = !empty($swapDates) ? array_values($swapDates) : null;
            } else {
                $cleanedData['swap_dates'] = null;
            }
            $cleanedData['swap_date'] = null;
            $cleanedData['swap_start_date'] = null;
            $cleanedData['swap_end_date'] = null;
        }

        $this->merge($cleanedData);
    }

    public function rules()
    {
        // Set uniform requirement: 48 hours (2 days) for all departments
        $minHours = 48;
        $minDays = 2;
        $minDate = now()->addDays($minDays)->toDateString();
        
        $swapType = $this->input('swap_type', 'single_date');

        $rules = [
            'requester_shift_id' => ['required','uuid', Rule::exists('worker_shifts','id')],
            'target_shift_id' => ['nullable','uuid', Rule::exists('worker_shifts','id')],
            'target_worker_id' => ['nullable','uuid', Rule::exists('workers','id')],
            'swap_type' => ['required', 'string', Rule::in(['single_date', 'date_range', 'recurring'])],
            'reason' => ['nullable','string','max:1000'],
            'expires_at' => ['nullable','date'],
        ];

        // Add conditional validation based on swap_type
        switch ($swapType) {
            case 'single_date':
                $rules['swap_date'] = ['required', 'date', "after_or_equal:{$minDate}"];
                break;
                
            case 'date_range':
                $rules['swap_start_date'] = ['required', 'date', "after_or_equal:{$minDate}"];
                $rules['swap_end_date'] = ['required', 'date', 'after:swap_start_date'];
                break;
                
            case 'recurring':
                $rules['swap_dates'] = ['required', 'array', 'min:1'];
                $rules['swap_dates.*'] = ['date', "after_or_equal:{$minDate}"];
                break;
        }

        return $rules;
    }

    public function messages()
    {
        // Uniform requirement: 48 hours (2 days) for all departments
        $minDays = 2;

        return [
            'requester_shift_id.required' => 'Pilih jadwal Anda yang ingin ditukar.',
            'requester_shift_id.exists' => 'Jadwal shift yang dipilih tidak valid.',
            'swap_type.required' => 'Pilih jenis tukar shift.',
            'swap_type.in' => 'Jenis tukar shift tidak valid.',
            
            // Single date messages
            'swap_date.required' => 'Pilih tanggal untuk tukar shift.',
            'swap_date.date' => 'Format tanggal tidak valid.',
            'swap_date.after_or_equal' => "Tanggal tukar shift harus minimal {$minDays} hari dari sekarang.",
            
            // Date range messages
            'swap_start_date.required' => 'Pilih tanggal mulai untuk rentang tukar shift.',
            'swap_start_date.date' => 'Format tanggal mulai tidak valid.',
            'swap_start_date.after_or_equal' => "Tanggal mulai harus minimal {$minDays} hari dari sekarang.",
            'swap_end_date.required' => 'Pilih tanggal akhir untuk rentang tukar shift.',
            'swap_end_date.date' => 'Format tanggal akhir tidak valid.',
            'swap_end_date.after' => 'Tanggal akhir harus setelah tanggal mulai.',
            
            // Recurring dates messages
            'swap_dates.required' => 'Pilih tanggal-tanggal untuk tukar shift berulang.',
            'swap_dates.array' => 'Format data tanggal tidak valid.',
            'swap_dates.min' => 'Minimal pilih satu tanggal untuk tukar shift berulang.',
            'swap_dates.*.date' => 'Format tanggal tidak valid.',
            'swap_dates.*.after_or_equal' => "Semua tanggal harus minimal {$minDays} hari dari sekarang.",
            
            'target_worker_id.exists' => 'Rekan kerja yang dipilih tidak valid.',
            'target_shift_id.exists' => 'Shift target yang dipilih tidak valid.',
        ];
    }
}
