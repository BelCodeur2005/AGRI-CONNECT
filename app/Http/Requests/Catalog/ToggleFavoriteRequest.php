<?php

// app/Http/Requests/Catalog/ToggleFavoriteRequest.php
namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class ToggleFavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isBuyer();
    }

    public function rules(): array
    {
        return [
            'offer_id' => ['required', 'exists:offers,id'],
        ];
    }
}