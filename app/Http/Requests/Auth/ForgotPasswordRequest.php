<?php

// app/Http/Requests/Auth/ForgotPasswordRequest.php
namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^(237)?6[5-9]\d{7}$/', 'exists:users,phone'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.exists' => 'Aucun compte n\'est associé à ce numéro de téléphone',
        ];
    }
}