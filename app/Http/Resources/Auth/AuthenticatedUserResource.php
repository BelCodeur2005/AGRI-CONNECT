<?php

// app/Http/Resources/Auth/AuthenticatedUserResource.php
namespace App\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthenticatedUserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'user' => new UserResource($this->resource),
            'profile' => $this->getProfileResource(),
            'profile_complete' => $this->hasCompleteProfile(),
            'missing_fields' => $this->getMissingProfileFields(),
            'permissions' => $this->getPermissions(),
        ];
    }

    private function getProfileResource()
    {
        return match($this->role->value) {
            'producer' => new \App\Http\Resources\Profile\ProducerResource($this->producer),
            'buyer' => new \App\Http\Resources\Profile\BuyerResource($this->buyer),
            'transporter' => new \App\Http\Resources\Profile\TransporterResource($this->transporter),
            default => null,
        };
    }

    private function getPermissions(): array
    {
        return [
            'can_create_offers' => $this->canAccessResource('offers'),
            'can_manage_cart' => $this->canAccessResource('cart'),
            'can_accept_deliveries' => $this->canAccessResource('deliveries'),
        ];
    }
}