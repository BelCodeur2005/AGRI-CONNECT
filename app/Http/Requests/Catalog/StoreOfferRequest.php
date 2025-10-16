<?php

// app/Http/Requests/Catalog/StoreOfferRequest.php
namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ProductUnit;
use Illuminate\Validation\Rules\Enum;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() 
            && auth()->user()->isProducer() 
            && auth()->user()->producer?->canCreateOffers();
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'title' => ['required', 'string', 'min:10', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'quantity_available' => ['required', 'numeric', 'min:1', 'max:100000'],
            'min_order_quantity' => ['nullable', 'numeric', 'min:1', 'lte:quantity_available'],
            'price_per_unit' => ['required', 'numeric', 'min:10', 'max:1000000'],
            'harvest_date' => ['nullable', 'date', 'before_or_equal:today'],
            'available_from' => ['required', 'date', 'after_or_equal:today'],
            'available_until' => ['required', 'date', 'after:available_from', 'before_or_equal:' . now()->addMonths(3)->toDateString()],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png', 'max:3072'],
            'quality_grade' => ['nullable', 'string', 'in:A,B,C,standard,premium,organic'],
            'organic' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.min' => 'Le titre doit contenir au moins 10 caractères',
            'quantity_available.min' => 'La quantité doit être d\'au moins 1',
            'quantity_available.max' => 'La quantité ne peut pas dépasser 100 000',
            'min_order_quantity.lte' => 'La quantité minimale ne peut pas dépasser la quantité disponible',
            'price_per_unit.min' => 'Le prix doit être d\'au moins 10 FCFA',
            'available_until.before_or_equal' => 'L\'offre ne peut pas être disponible plus de 3 mois à l\'avance',
            'photos.max' => 'Vous ne pouvez télécharger que 5 photos maximum',
            'photos.*.max' => 'Chaque photo ne doit pas dépasser 3 Mo',
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => 'produit',
            'location_id' => 'localisation',
            'quantity_available' => 'quantité disponible',
            'min_order_quantity' => 'quantité minimale',
            'price_per_unit' => 'prix unitaire',
            'harvest_date' => 'date de récolte',
            'available_from' => 'disponible à partir du',
            'available_until' => 'disponible jusqu\'au',
        ];
    }
}
