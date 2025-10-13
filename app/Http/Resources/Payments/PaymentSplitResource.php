<?php

// app/Http/Resources/Payments/PaymentSplitResource.php
namespace App\Http\Resources\Payments;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentSplitResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'producer' => $this->when($this->relationLoaded('producer'), [
                'id' => $this->producer->id,
                'name' => $this->producer->user->name,
                'farm_name' => $this->producer->farm_name,
            ]),
            'amount' => (float) $this->amount,
            'platform_commission' => (float) $this->platform_commission,
            'net_amount' => (float) $this->net_amount,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'transaction_reference' => $this->transaction_reference,
            'released_at' => $this->released_at?->toIso8601String(),
        ];
    }
}
