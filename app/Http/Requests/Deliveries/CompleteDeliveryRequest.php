<?php

// app/Http/Requests/Deliveries/CompleteDeliveryRequest.php
namespace App\Http\Requests\Deliveries;

use Illuminate\Foundation\Http\FormRequest;

class CompleteDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $delivery = $this->route('delivery');
        
        return auth()->check()
            && auth()->user()->isTransporter()
            && $delivery->transporter_id === auth()->user()->transporter->id
            && in_array($delivery->status, [\App\Enums\DeliveryStatus::IN_TRANSIT, \App\Enums\DeliveryStatus::ARRIVED]);
    }

    public function rules(): array
    {
        return [
            'delivery_proof_photo' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:5120'],
            'signature' => ['nullable', 'string'], // Base64 encoded signature
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:500'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_phone' => ['nullable', 'string', 'regex:/^(237)?6[5-9]\d{7}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'delivery_proof_photo.required' => 'Une photo de preuve de livraison est requise',
            'delivery_proof_photo.max' => 'La photo ne doit pas dépasser 5 Mo',
        ];
    }
}