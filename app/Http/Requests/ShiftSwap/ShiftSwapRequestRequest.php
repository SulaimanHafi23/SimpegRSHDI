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

    public function rules()
    {
        return [
            'requester_shift_id' => ['required','uuid', Rule::exists('worker_shifts','id')],
            'target_shift_id' => ['nullable','uuid', Rule::exists('worker_shifts','id')],
            'target_worker_id' => ['nullable','uuid', Rule::exists('workers','id')],
            'reason' => ['nullable','string','max:1000'],
            'expires_at' => ['nullable','date'],
        ];
    }

    public function messages()
    {
        return [
            'requester_shift_id.required' => 'Pilih jadwal Anda yang ingin ditukar.',
        ];
    }
}
