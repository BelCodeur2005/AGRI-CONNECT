<?php

// app/Http/Requests/Ratings/DeleteRatingRequest.php
namespace App\Http\Requests\Ratings;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $rating = $this->route('rating');
        
        return auth()->check()
            && (
                $rating->rater_id === auth()->id() ||
                auth()->user()->isAdmin()
            )
            && $rating->created_at->gt(now()->subHours(72)); // Peut supprimer dans les 72h
    }

    public function rules(): array
    {
        return [];
    }
}
