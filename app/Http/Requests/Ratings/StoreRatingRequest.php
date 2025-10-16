<?php

// app/Http/Requests/Ratings/StoreRatingRequest.php
namespace App\Http\Requests\Ratings;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');
        
        return auth()->check()
            && $order->canBeRated()
            && (
                $order->buyer->user_id === auth()->id() ||
                $order->items->pluck('producer.user_id')->contains(auth()->id()) ||
                $order->delivery?->transporter->user_id === auth()->id()
            );
    }

    public function rules(): array
    {
        return [
            'rateable_type' => ['required', 'string', 'in:App\Models\Producer,App\Models\Buyer,App\Models\Transporter'],
            'rateable_id' => ['required', 'integer'],
            'overall_score' => ['required', 'integer', 'between:1,5'],
            'quality_score' => ['nullable', 'integer', 'between:1,5'],
            'punctuality_score' => ['nullable', 'integer', 'between:1,5'],
            'communication_score' => ['nullable', 'integer', 'between:1,5'],
            'packaging_score' => ['nullable', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'overall_score.required' => 'La note globale est requise',
            'overall_score.between' => 'La note doit être entre 1 et 5 étoiles',
            'comment.min' => 'Le commentaire doit contenir au moins 10 caractères',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Vérifier que l'utilisateur n'a pas déjà noté cette entité pour cette commande
            $existingRating = \App\Models\Rating::where('order_id', $this->route('order')->id)
                ->where('rater_id', auth()->id())
                ->where('rateable_type', $this->rateable_type)
                ->where('rateable_id', $this->rateable_id)
                ->exists();

            if ($existingRating) {
                $validator->errors()->add('rateable_id', 'Vous avez déjà noté cette personne pour cette commande');
            }

            // Vérifier que rateable_id existe pour le type donné
            $model = $this->rateable_type;
            if (class_exists($model)) {
                if (!$model::find($this->rateable_id)) {
                    $validator->errors()->add('rateable_id', 'L\'entité à noter n\'existe pas');
                }
            }
        });
    }
}