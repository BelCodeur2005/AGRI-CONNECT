<?php

// app/Http/Requests/Auth/VerifyPhoneRequest.php
namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPhoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^(237)?6[5-9]\d{7}$/'],
            'code' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.size' => 'Le code doit contenir exactement 6 chiffres',
            'code.regex' => 'Le code doit être composé uniquement de chiffres',
        ];
    }
}
