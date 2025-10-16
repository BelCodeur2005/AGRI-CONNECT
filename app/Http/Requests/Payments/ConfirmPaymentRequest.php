<?php

// app/Http/Requests/Payments/ConfirmPaymentRequest.php
namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isBuyer();
    }

    public function rules(): array
    {
        return [
            'transaction_id' => ['required', 'string', 'exists:payments,transaction_id'],
            'operator_reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}