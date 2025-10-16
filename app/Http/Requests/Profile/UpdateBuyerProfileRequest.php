<?php

// app/Http/Requests/Profile/UpdateBuyerProfileRequest.php
namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBuyerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isBuyer();
    }

    public function rules(): array
    {
        return [
            'business_name' => ['sometimes', 'string', 'max:255'],
            'business_type' => [
                'sometimes',
                'string',
                'in:restaurant,hotel,canteen,processor,retailer,exporter,cooperative'
            ],
            'location_id' => ['sometimes', 'exists:locations,id'],
            'delivery_address' => ['sometimes', 'string', 'max:500'],
            'business_license' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'stars_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'preferred_delivery_time' => ['nullable', 'string', 'in:morning,afternoon,evening,anytime'],
        ];
    }

    public function messages(): array
    {
        return [
            'business_type.in' => 'Type d\'entreprise invalide',
            'stars_rating.between' => 'La note doit être entre 1 et 5 étoiles',
        ];
    }
}
