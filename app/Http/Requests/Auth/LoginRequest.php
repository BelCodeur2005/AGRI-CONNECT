<?php

// app/Http/Requests/Auth/LoginRequest.php
namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^(237)?6[5-9]\d{7}$/'],
            'password' => 'required|string',
            'fcm_token' => ['nullable', 'string', 'max:500'],
            'revoke_other_tokens' => 'nullable|boolean',
        ];
    }
}
