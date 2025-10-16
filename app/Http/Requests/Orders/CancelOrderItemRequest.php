<?php

// app/Http/Requests/Orders/CancelOrderItemRequest.php
namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class CancelOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $orderItem = $this->route('orderItem');
        
        return auth()->check()
            && auth()->user()->isProducer()
            && $orderItem->producer_id === auth()->user()->producer->id
            && $orderItem->status->canBeCancelled();
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
