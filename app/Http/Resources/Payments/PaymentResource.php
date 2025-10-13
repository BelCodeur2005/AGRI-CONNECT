<?php

// app/Http/Resources/Payments/PaymentResource.php
namespace App\Http\Resources\Payments;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'transaction_id' => $this->transaction_id,
            'amount' => (float) $this->amount,
            'method' => [
                'value' => $this->method->value,
                'label' => $this->method->label(),
            ],
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'payer_phone' => $this->payer_phone,
            'operator_reference' => $this->operator_reference,
            'splits' => PaymentSplitResource::collection($this->whenLoaded('splits')),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'held_at' => $this->held_at?->toIso8601String(),
            'released_at' => $this->released_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

