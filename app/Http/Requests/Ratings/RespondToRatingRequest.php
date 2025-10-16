<?php

// app/Http/Requests/Ratings/RespondToRatingRequest.php
namespace App\Http\Requests\Ratings;

use Illuminate\Foundation\Http\FormRequest;

class RespondToRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $rating = $this->route('rating');
        
        return auth()->check()
            && (
                $rating->rateable->user_id === auth()->id() ||
                auth()->user()->isAdmin()
            );
    }

    public function rules(): array
    {
        return [
            'admin_response' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'admin_response.min' => 'La réponse doit contenir au moins 10 caractères',
        ];
    }
}