<?php

// app/Http/Requests/Auth/ResetPasswordRequest.php
namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^(237)?6[5-9]\d{7}$/', 'exists:users,phone'],
            'code' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ];
    }
}
