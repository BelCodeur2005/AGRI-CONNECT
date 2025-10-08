<?php

// app/Http/Requests/OfferRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isProducer();
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'location_id' => 'required|exists:locations,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity_available' => 'required|numeric|min:1',
            'min_order_quantity' => 'nullable|numeric|min:1',
            'price_per_unit' => 'required|numeric|min:1',
            'harvest_date' => 'nullable|date',
            'available_from' => 'required|date|after_or_equal:today',
            'available_until' => 'required|date|after:available_from',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|max:2048',
            'quality_grade' => 'nullable|in:A,B,C',
            'organic' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Le produit est obligatoire',
            'product_id.exists' => 'Ce produit n\'existe pas',
            'quantity_available.required' => 'La quantité disponible est obligatoire',
            'price_per_unit.required' => 'Le prix unitaire est obligatoire',
            'available_from.after_or_equal' => 'La date de disponibilité doit être aujourd\'hui ou dans le futur',
            'available_until.after' => 'La date de fin doit être après la date de début',
        ];
    }
}