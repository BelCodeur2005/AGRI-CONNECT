<?php

// app/Http/Requests/Cart/UpdateCartItemRequest.php
namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $cartItem = $this->route('cartItem');
        
        return auth()->check() 
            && auth()->user()->isBuyer()
            && $cartItem->user_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'numeric', 'min:0', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.min' => 'La quantité doit être supérieure ou égale à 0 (0 pour supprimer)',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->quantity > 0) {
                $cartItem = $this->route('cartItem');
                $offer = $cartItem->offer;
                
                if (!$offer->canOrder($this->quantity)) {
                    if ($offer->remaining_quantity < $this->quantity) {
                        $validator->errors()->add('quantity', "Stock insuffisant. Disponible: {$offer->remaining_quantity}");
                    }
                }
            }
        });
    }
}