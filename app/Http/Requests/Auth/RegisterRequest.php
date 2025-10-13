<?php

// app/Http/Requests/Auth/RegisterRequest.php
namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone|regex:/^6[0-9]{8}$/', // Format CM
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:producer,buyer,transporter',
        ];

        // Validation spécifique selon le rôle
        if ($this->role === 'producer') {
            $rules += [
                'location_id' => 'required|exists:locations,id',
                'farm_name' => 'nullable|string|max:255',
                'farm_address' => 'required|string',
            ];
        }

        if ($this->role === 'buyer') {
            $rules += [
                'location_id' => 'required|exists:locations,id',
                'business_name' => 'required|string|max:255',
                'business_type' => 'required|in:restaurant,hotel,supermarket,processor,exporter,other',
                'delivery_address' => 'required|string',
            ];
        }

        if ($this->role === 'transporter') {
            $rules += [
                'vehicle_type' => 'required|string|max:255',
                'vehicle_registration' => 'required|string|max:255',
                'driver_license_number' => 'required|string|max:255',
                'max_capacity_kg' => 'required|numeric|min:50',
            ];
        }   

        return $rules;
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Le numéro de téléphone doit commencer par 6 et contenir 9 chiffres',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé',
            'email.unique' => 'Cet email est déjà utilisé',
        ];
    }
}