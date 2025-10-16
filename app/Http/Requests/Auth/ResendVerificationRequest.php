<?php

// app/Http/Requests/Auth/ResendVerificationRequest.php
namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResendVerificationRequest extends FormRequest
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
}
