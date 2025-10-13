<?php

// app/Http/Resources/Auth/UserResource.php
namespace App\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role->value,
            'role_label' => $this->role->label(),
            'profile_photo_url' => $this->profile_photo_url,
            'is_active' => $this->is_active,
            'is_verified' => $this->is_verified,
            'phone_verified' => (bool) $this->phone_verified_at,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}