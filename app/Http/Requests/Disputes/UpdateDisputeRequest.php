<?php

// app/Http/Requests/Disputes/UpdateDisputeRequest.php
namespace App\Http\Requests\Disputes;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $dispute = $this->route('dispute');
        
        return auth()->check()
            && $dispute->reported_by === auth()->id()
            && $dispute->status->isActive();
    }

    public function rules(): array
    {
        return [
            'description' => ['sometimes', 'string', 'min:50', 'max:2000'],
            'evidence_photos' => ['sometimes', 'array', 'max:10'],
            'evidence_photos.*' => ['image', 'mimes:jpeg,jpg,png', 'max:5120'],
        ];
    }
}
