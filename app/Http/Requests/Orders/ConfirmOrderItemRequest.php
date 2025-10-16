<?php

// app/Http/Requests/Orders/ConfirmOrderItemRequest.php
namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $orderItem = $this->route('orderItem');
        
        return auth()->check()
            && auth()->user()->isProducer()
            && $orderItem->producer_id === auth()->user()->producer->id
            && $orderItem->status->canBeConfirmed();
    }

    public function rules(): array
    {
        return [
            'producer_notes' => ['nullable', 'string', 'max:500'],
            'expected_ready_date' => ['nullable', 'date', 'after_or_equal:today', 'before_or_equal:' . now()->addDays(7)->toDateString()],
        ];
    }

    public function messages(): array
    {
        return [
            'expected_ready_date.after_or_equal' => 'La date de préparation ne peut pas être dans le passé',
            'expected_ready_date.before_or_equal' => 'La date de préparation ne peut pas dépasser 7 jours',
        ];
    }
}

