<?php

// app/Http/Requests/Deliveries/ReportIssueRequest.php
namespace App\Http\Requests\Deliveries;

use Illuminate\Foundation\Http\FormRequest;

class ReportIssueRequest extends FormRequest
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
            'issue_type' => ['required', 'string', 'in:road_block,vehicle_breakdown,weather,accident,address_issue,customer_unavailable,other'],
            'description' => ['required', 'string', 'min:20', 'max:1000'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png', 'max:3072'],
            'estimated_delay_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.min' => 'La description doit contenir au moins 20 caractères',
            'photos.max' => 'Vous ne pouvez télécharger que 5 photos maximum',
            'estimated_delay_minutes.max' => 'Le délai ne peut pas dépasser 24 heures (1440 minutes)',
        ];
    }
}