<?php

// app/Http/Requests/Auth/RegisterRequest.php
namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\UserRole;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'phone' => [
                'required',
                'string',
                'regex:/^(237)?6[5-9]\d{7}$/',
                'unique:users,phone'
            ],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
            ],
            'role' => ['required', new Enum(UserRole::class)],
            
            // Producer fields
            'farm_name' => ['required_if:role,producer', 'string', 'max:255'],
            'location_id' => ['required_if:role,producer,buyer', 'exists:locations,id'],
            'farm_address' => ['required_if:role,producer', 'string', 'max:500'],
            
            // Buyer fields
            'business_name' => ['required_if:role,buyer', 'string', 'max:255'],
            'business_type' => ['required_if:role,buyer', 'string', 'in:restaurant,hotel,canteen,processor,retailer'],
            'delivery_address' => ['required_if:role,buyer', 'string', 'max:500'],
            
            // Transporter fields
            'vehicle_type' => ['required_if:role,transporter', 'string', 'in:pickup,truck,van,motorcycle'],
            'vehicle_registration' => ['required_if:role,transporter', 'string', 'max:50'],
            'max_capacity_kg' => ['required_if:role,transporter', 'numeric', 'min:50', 'max:5000'],
            'driver_license_number' => ['required_if:role,transporter', 'string', 'max:255'],

        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Le numéro de téléphone doit être un numéro camerounais valide (ex: 651712856 ou 237651712856)',
            'farm_name.required_if' => 'Le nom de la ferme est requis pour les producteurs',
            'business_name.required_if' => 'Le nom de l\'entreprise est requis pour les acheteurs',
            'vehicle_type.required_if' => 'Le type de véhicule est requis pour les transporteurs',
        ];
    }
}