<?php

// app/Http/Requests/Deliveries/AssignDeliveryRequest.php
namespace App\Http\Requests\Deliveries;

use Illuminate\Foundation\Http\FormRequest;

class AssignDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'transporter_id' => ['required', 'exists:transporters,id'],
            'scheduled_pickup_at' => ['required', 'date', 'after_or_equal:now'],
            'scheduled_delivery_at' => ['required', 'date', 'after:scheduled_pickup_at'],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'scheduled_pickup_at.after_or_equal' => 'L\'heure de collecte ne peut pas être dans le passé',
            'scheduled_delivery_at.after' => 'L\'heure de livraison doit être après l\'heure de collecte',
        ];
    }
}
