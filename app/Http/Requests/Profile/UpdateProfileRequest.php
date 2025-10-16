<?php

// app/Http/Requests/Profile/UpdateProfileRequest.php
namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $userId = auth()->id();

        return [
            'name' => ['sometimes', 'string', 'min:3', 'max:255'],
            'email' => [
                'sometimes',
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'phone' => [
                'sometimes',
                'string',
                'regex:/^(237)?6[5-9]\d{7}$/',
                Rule::unique('users', 'phone')->ignore($userId)
            ],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
            'fcm_token' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'profile_photo.max' => 'La photo de profil ne doit pas dépasser 2 Mo',
            'phone.regex' => 'Le numéro de téléphone doit être un numéro camerounais valide',
        ];
    }
}