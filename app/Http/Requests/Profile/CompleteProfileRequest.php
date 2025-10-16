<?php

// app/Http/Requests/Profile/CompleteProfileRequest.php
namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class CompleteProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && !auth()->user()->hasCompleteProfile();
    }

    public function rules(): array
    {
        $user = auth()->user();
        $rules = [];

        if ($user->isProducer()) {
            $rules = array_merge($rules, [
                'location_id' => ['required', 'exists:locations,id'],
                'farm_name' => ['required', 'string', 'max:255'],
                'farm_address' => ['required', 'string', 'max:500'],
                'farm_size' => ['nullable', 'numeric', 'min:0'],
                'years_experience' => ['nullable', 'integer', 'min:0'],
            ]);
        }

        if ($user->isBuyer()) {
            $rules = array_merge($rules, [
                'location_id' => ['required', 'exists:locations,id'],
                'business_name' => ['required', 'string', 'max:255'],
                'business_type' => ['required', 'string', 'in:restaurant,hotel,canteen,processor,retailer'],
                'delivery_address' => ['required', 'string', 'max:500'],
            ]);
        }

        if ($user->isTransporter()) {
            $rules = array_merge($rules, [
                'vehicle_type' => ['required', 'string', 'in:pickup,truck,van,motorcycle'],
                'vehicle_registration' => ['required', 'string', 'max:50'],
                'driver_license_number' => ['required', 'string', 'max:50'],
                'max_capacity_kg' => ['required', 'numeric', 'min:50'],
            ]);
        }

        return $rules;
    }
}