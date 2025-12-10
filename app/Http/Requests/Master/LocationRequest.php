<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocationRequest extends FormRequest
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
        $locationId = $this->route('location');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('locations', 'name')->ignore($locationId),
            ],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'integer', 'min:10', 'max:1000'],
            'enforce_geofence' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lokasi harus diisi.',
            'name.unique' => 'Lokasi ini sudah ada.',
            'name.max' => 'Nama lokasi maksimal 100 karakter.',
            'latitude.between' => 'Latitude harus antara -90 dan 90.',
            'longitude.between' => 'Longitude harus antara -180 dan 180.',
            'radius.required' => 'Radius harus diisi.',
            'radius.min' => 'Radius minimal 10 meter.',
            'radius.max' => 'Radius maksimal 1000 meter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'enforce_geofence' => $this->has('enforce_geofence') ? 
                filter_var($this->enforce_geofence, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false : 
                false,
            'is_active' => $this->has('is_active') ? 
                filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false : 
                false,
        ]);
    }
}
