<?php

// app/Http/Requests/Payments/ReleasePaymentRequest.php
namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class ReleasePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');
        
        return auth()->check()
            && (
                ($order->buyer->user_id === auth()->id() && $order->status === \App\Enums\OrderStatus::DELIVERED) ||
                auth()->user()->isAdmin()
            );
    }

    public function rules(): array
    {
        return [
            'confirm_delivery' => ['required', 'boolean', 'accepted'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirm_delivery.accepted' => 'Vous devez confirmer avoir reçu la livraison',
        ];
    }
}