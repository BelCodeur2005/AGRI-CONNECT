<?php

// app/Http/Requests/Ratings/UpdateRatingRequest.php
namespace App\Http\Requests\Ratings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $rating = $this->route('rating');
        
        return auth()->check()
            && $rating->rater_id === auth()->id()
            && $rating->created_at->gt(now()->subHours(48)); // Peut modifier dans les 48h
    }

    public function rules(): array
    {
        return [
            'overall_score' => ['sometimes', 'integer', 'between:1,5'],
            'quality_score' => ['sometimes', 'nullable', 'integer', 'between:1,5'],
            'punctuality_score' => ['sometimes', 'nullable', 'integer', 'between:1,5'],
            'communication_score' => ['sometimes', 'nullable', 'integer', 'between:1,5'],
            'packaging_score' => ['sometimes', 'nullable', 'integer', 'between:1,5'],
            'comment' => ['sometimes', 'nullable', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'overall_score.between' => 'La note doit être entre 1 et 5 étoiles',
            'comment.min' => 'Le commentaire doit contenir au moins 10 caractères',
        ];
    }
}
