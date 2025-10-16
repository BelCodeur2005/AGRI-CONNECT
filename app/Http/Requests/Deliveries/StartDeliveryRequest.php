<?php

// app/Http/Requests/Deliveries/StartDeliveryRequest.php
namespace App\Http\Requests\Deliveries;

use Illuminate\Foundation\Http\FormRequest;

class StartDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $delivery = $this->route('delivery');
        
        return auth()->check()
            && auth()->user()->isTransporter()
            && $delivery->transporter_id === auth()->user()->transporter->id
            && $delivery->status === \App\Enums\DeliveryStatus::ASSIGNED;
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}