<?php
// app/Http/Requests/Deliveries/AcceptDeliveryRequest.php
namespace App\Http\Requests\Deliveries;

use Illuminate\Foundation\Http\FormRequest;

class AcceptDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isTransporter();
    }

    public function rules(): array
    {
        return [
            'delivery_id' => ['required', 'exists:deliveries,id'],
            'estimated_pickup_time' => ['nullable', 'date', 'after_or_equal:now'],
            'estimated_delivery_time' => ['nullable', 'date', 'after:estimated_pickup_time'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}