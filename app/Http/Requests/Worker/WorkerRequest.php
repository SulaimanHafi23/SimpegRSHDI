<?php

namespace App\Http\Requests\Worker;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkerRequest extends FormRequest
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $workerId = $this->route('id');

        return [
            'nip' => [
                'required',
                'string',
                'max:50',
                Rule::unique('workers', 'nip')->ignore($workerId),
            ],
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('workers', 'email')->ignore($workerId),
            ],
            'phone' => 'required|string|max:20',
            'birth_place' => 'required|string|max:100',
            'birth_date' => 'required|date|before:today',
            'address' => 'required|string|max:500',
            'religion_id' => ['required', Rule::exists('religions', 'id')],
            'gender_id' => ['required', Rule::exists('genders', 'id')],
            'department_id' => ['required', Rule::exists('positions', 'id')],
            'location_id' => ['required', Rule::exists('locations', 'id')],
            'join_date' => 'required|date',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'is_active' => 'boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'nip' => 'NIP',
            'name' => 'Nama',
            'email' => 'Email',
            'phone' => 'No. Telepon',
            'birth_place' => 'Tempat Lahir',
            'birth_date' => 'Tanggal Lahir',
            'address' => 'Alamat',
            'religion_id' => 'Agama',
            'gender_id' => 'Jenis Kelamin',
            'position_id' => 'Jabatan',
            'location_id' => 'Lokasi',
            'join_date' => 'Tanggal Bergabung',
            'photo' => 'Foto',
            'is_active' => 'Status Aktif',
        ];
    }
}
