<?php

// app/Http/Requests/Cart/AddToCartRequest.php
namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isBuyer();
    }

    public function rules(): array
    {
        return [
            'offer_id' => ['required', 'exists:offers,id'],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'offer_id.required' => 'L\'offre est requise',
            'offer_id.exists' => 'Cette offre n\'existe pas',
            'quantity.min' => 'La quantité doit être supérieure à 0',
            'quantity.max' => 'La quantité ne peut pas dépasser 100 000',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $offer = \App\Models\Offer::find($this->offer_id);
            
            if ($offer && !$offer->canOrder($this->quantity)) {
                if ($offer->status !== \App\Enums\OfferStatus::ACTIVE) {
                    $validator->errors()->add('offer_id', 'Cette offre n\'est plus disponible');
                } elseif ($offer->remaining_quantity < $this->quantity) {
                    $validator->errors()->add('quantity', "Stock insuffisant. Quantité disponible: {$offer->remaining_quantity}");
                } elseif ($offer->min_order_quantity && $this->quantity < $offer->min_order_quantity) {
                    $validator->errors()->add('quantity', "Quantité minimale requise: {$offer->min_order_quantity}");
                }
            }
        });
    }
}
