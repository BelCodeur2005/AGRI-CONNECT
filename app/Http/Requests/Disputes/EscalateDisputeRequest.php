<?php

// app/Http/Requests/Disputes/EscalateDisputeRequest.php
namespace App\Http\Requests\Disputes;

use Illuminate\Foundation\Http\FormRequest;

class EscalateDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $dispute = $this->route('dispute');
        
        return auth()->check()
            && (
                $dispute->reported_by === auth()->id() ||
                $dispute->reported_against === auth()->id()
            )
            && $dispute->status === \App\Enums\DisputeStatus::INVESTIGATING;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:20', 'max:1000'],
            'additional_evidence' => ['nullable', 'array', 'max:5'],
            'additional_evidence.*' => ['image', 'mimes:jpeg,jpg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.min' => 'La raison de l\'escalade doit contenir au moins 20 caractères',
        ];
    }
}