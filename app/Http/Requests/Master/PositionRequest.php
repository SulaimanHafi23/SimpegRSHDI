<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PositionRequest extends FormRequest
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
        $positionId = $this->route('position');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('positions', 'name')->ignore($positionId)->whereNull('deleted_at'),
            ],
            'description' => 'nullable|string|max:500',
            'level' => 'nullable|integer|min:1|max:100',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Jabatan',
            'description' => 'Deskripsi',
            'level' => 'Level',
            'is_active' => 'Status Aktif',
        ];
    }
}
