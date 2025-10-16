<?php

// app/Http/Requests/Payments/InitiatePaymentRequest.php
namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\PaymentMethod;
use Illuminate\Validation\Rules\Enum;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');
        
        return auth()->check()
            && $order->buyer->user_id === auth()->id()
            && $order->status === \App\Enums\OrderStatus::CONFIRMED;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', new Enum(PaymentMethod::class)],
            'phone' => [
                'required_if:payment_method,orange_money,mtn_momo',
                'nullable',
                'string',
                'regex:/^(237)?6[5-9]\d{7}$/'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'La méthode de paiement est requise',
            'phone.required_if' => 'Le numéro de téléphone est requis pour le paiement mobile',
            'phone.regex' => 'Le numéro de téléphone doit être un numéro camerounais valide',
        ];
    }
}