<?php

// app/Http/Requests/RatingRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'overall_score' => 'required|integer|min:1|max:5',
            'quality_score' => 'nullable|integer|min:1|max:5',
            'punctuality_score' => 'nullable|integer|min:1|max:5',
            'communication_score' => 'nullable|integer|min:1|max:5',
            'packaging_score' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'overall_score.required' => 'La note globale est obligatoire',
            'overall_score.min' => 'La note minimale est 1',
            'overall_score.max' => 'La note maximale est 5',
        ];
    }
}