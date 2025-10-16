<?php

// app/Http/Requests/Profile/UpdateTransporterProfileRequest.php
namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransporterProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isTransporter();
    }

    public function rules(): array
    {
        return [
            'vehicle_type' => ['sometimes', 'string', 'in:pickup,truck,van,motorcycle,bicycle'],
            'vehicle_registration' => ['sometimes', 'string', 'max:50'],
            'vehicle_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:3072'],
            'driver_license_number' => ['sometimes', 'string', 'max:50'],
            'driver_license_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,pdf', 'max:5120'],
            'max_capacity_kg' => ['sometimes', 'numeric', 'min:10', 'max:10000'],
            'has_refrigeration' => ['sometimes', 'boolean'],
            'service_areas' => ['nullable', 'array'],
            'service_areas.*' => ['integer', 'exists:locations,id'],
            'is_available' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_photo.max' => 'La photo du véhicule ne doit pas dépasser 3 Mo',
            'driver_license_photo.max' => 'La photo du permis ne doit pas dépasser 5 Mo',
            'max_capacity_kg.min' => 'La capacité minimale est de 10 kg',
            'max_capacity_kg.max' => 'La capacité maximale est de 10 000 kg (10 tonnes)',
        ];
    }
}
