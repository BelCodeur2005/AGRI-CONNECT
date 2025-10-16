<?php

// app/Http/Requests/Catalog/SearchOffersRequest.php
namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class SearchOffersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'exists:products,id'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'producer_id' => ['nullable', 'exists:producers,id'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            'min_quantity' => ['nullable', 'numeric', 'min:0'],
            'organic' => ['nullable', 'boolean'],
            'quality_grade' => ['nullable', 'string', 'in:A,B,C,standard,premium,organic'],
            'available_from' => ['nullable', 'date'],
            'available_until' => ['nullable', 'date', 'after_or_equal:available_from'],
            'sort_by' => ['nullable', 'string', 'in:price_asc,price_desc,quantity,recent,rating,distance'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'max_price.gte' => 'Le prix maximum doit être supérieur au prix minimum',
            'available_until.after_or_equal' => 'La date de fin doit être après la date de début',
            'per_page.max' => 'Le nombre maximum d\'éléments par page est 100',
        ];
    }
}