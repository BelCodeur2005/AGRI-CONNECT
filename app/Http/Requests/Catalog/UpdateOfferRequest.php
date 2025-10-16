<?php

// app/Http/Requests/Catalog/UpdateOfferRequest.php
namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        $offer = $this->route('offer');
        
        return auth()->check() 
            && auth()->user()->isProducer()
            && $offer->producer_id === auth()->user()->producer->id;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'min:10', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'quantity_available' => ['sometimes', 'numeric', 'min:1', 'max:100000'],
            'min_order_quantity' => ['sometimes', 'nullable', 'numeric', 'min:1'],
            'price_per_unit' => ['sometimes', 'numeric', 'min:10', 'max:1000000'],
            'available_from' => ['sometimes', 'date', 'after_or_equal:today'],
            'available_until' => ['sometimes', 'date', 'after:available_from'],
            'photos' => ['sometimes', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png', 'max:3072'],
            'quality_grade' => ['sometimes', 'nullable', 'string', 'in:A,B,C,standard,premium,organic'],
            'organic' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'photos.max' => 'Vous ne pouvez télécharger que 5 photos maximum',
            'photos.*.max' => 'Chaque photo ne doit pas dépasser 3 Mo',
        ];
    }
}