<?php

// app/Http/Requests/Catalog/BrowseOffersRequest.php
namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class BrowseOffersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'featured' => ['nullable', 'boolean'],
            'trending' => ['nullable', 'boolean'],
            'new_arrivals' => ['nullable', 'boolean'],
            'organic_only' => ['nullable', 'boolean'],
            'sort_by' => ['nullable', 'string', 'in:price_asc,price_desc,quantity,recent,rating'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ];
    }
}
