<?php

// app/Http/Requests/Cart/CheckoutCartRequest.php
namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isBuyer();
    }

    public function rules(): array
    {
        return [
            'delivery_address' => ['required', 'string', 'max:500'],
            'delivery_location_id' => ['required', 'exists:locations,id'],
            'requested_delivery_date' => ['required', 'date', 'after:today', 'before_or_equal:' . now()->addDays(30)->toDateString()],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'string', 'in:orange_money,mtn_momo,cash'],
        ];
    }

    public function messages(): array
    {
        return [
            'requested_delivery_date.after' => 'La date de livraison doit être après aujourd\'hui',
            'requested_delivery_date.before_or_equal' => 'La date de livraison ne peut pas dépasser 30 jours',
        ];
    }
}
