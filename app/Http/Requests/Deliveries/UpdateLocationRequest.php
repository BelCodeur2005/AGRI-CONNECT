<?php

// app/Http/Requests/Deliveries/UpdateLocationRequest.php
namespace App\Http\Requests\Deliveries;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $delivery = $this->route('delivery');
        
        return auth()->check()
            && auth()->user()->isTransporter()
            && $delivery->transporter_id === auth()->user()->transporter->id;
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.between' => 'La latitude doit être entre -90 et 90',
            'longitude.between' => 'La longitude doit être entre -180 et 180',
        ];
    }
}