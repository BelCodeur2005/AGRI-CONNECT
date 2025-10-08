<?php

// app/Http/Requests/OrderRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isBuyer();
    }

    public function rules(): array
    {
        return [
            'offer_id' => 'required|exists:offers,id',
            'quantity' => 'required|numeric|min:1',
            'delivery_address' => 'required|string',
            'delivery_location_id' => 'required|exists:locations,id',
            'requested_delivery_date' => 'nullable|date|after:today',
            'delivery_notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:orange_money,mtn_momo,cash,bank_transfer',
        ];
    }

    public function messages(): array
    {
        return [
            'offer_id.required' => 'L\'offre est obligatoire',
            'offer_id.exists' => 'Cette offre n\'existe pas',
            'quantity.required' => 'La quantité est obligatoire',
            'quantity.min' => 'La quantité minimale est 1',
            'delivery_address.required' => 'L\'adresse de livraison est obligatoire',
            'payment_method.required' => 'Le mode de paiement est obligatoire',
        ];
    }
}
