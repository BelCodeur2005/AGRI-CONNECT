<?php

// app/Events/Auth/UserRegistered.php
namespace App\Events\Auth;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public string $verificationCode
    ) {}
}