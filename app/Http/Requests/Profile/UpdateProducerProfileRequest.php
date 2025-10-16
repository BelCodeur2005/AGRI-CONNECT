<?php

// app/Http/Requests/Profile/UpdateProducerProfileRequest.php
namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProducerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isProducer();
    }

    public function rules(): array
    {
        return [
            'farm_name' => ['sometimes', 'string', 'max:255'],
            'location_id' => ['sometimes', 'exists:locations,id'],
            'farm_size' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'farm_address' => ['sometimes', 'string', 'max:500'],
            'id_card_number' => ['nullable', 'string', 'max:50'],
            'id_card_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,pdf', 'max:5120'],
            'certifications' => ['nullable', 'array'],
            'certifications.*' => ['string', 'max:255'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_card_photo.max' => 'La photo de la pièce d\'identité ne doit pas dépasser 5 Mo',
            'farm_size.max' => 'La taille de la ferme ne peut pas dépasser 10 000 hectares',
        ];
    }
}
