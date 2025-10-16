<?php

// app/Http/Requests/Orders/CancelOrderRequest.php
namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class CancelOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');
        
        return auth()->check()
            && (
                ($order->buyer_id === auth()->user()->buyer?->id) ||
                auth()->user()->isAdmin()
            )
            && $order->canBeCancelled();
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Vous devez indiquer la raison de l\'annulation',
            'reason.min' => 'La raison doit contenir au moins 10 caractères',
        ];
    }
}
