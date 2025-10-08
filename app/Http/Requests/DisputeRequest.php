<?php

// app/Http/Requests/DisputeRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'type' => 'required|in:quality,quantity,delay,damage,other',
            'description' => 'required|string|min:20',
            'evidence_photos' => 'nullable|array|max:5',
            'evidence_photos.*' => 'image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'description.min' => 'La description doit contenir au moins 20 caractères',
            'type.required' => 'Le type de litige est obligatoire',
        ];
    }
}
