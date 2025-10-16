<?php

// app/Http/Requests/Disputes/CloseDisputeRequest.php
namespace App\Http\Requests\Disputes;

use Illuminate\Foundation\Http\FormRequest;

class CloseDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $dispute = $this->route('dispute');
        
        return auth()->check()
            && (
                $dispute->reported_by === auth()->id() ||
                auth()->user()->isAdmin()
            )
            && $dispute->status === \App\Enums\DisputeStatus::RESOLVED;
    }

    public function rules(): array
    {
        return [
            'satisfied' => ['required', 'boolean'],
            'feedback' => ['nullable', 'string', 'max:500'],
        ];
    }
}
