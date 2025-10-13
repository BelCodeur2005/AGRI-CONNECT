<?php

// app/Listeners/Auth/SendVerificationSMS.php
namespace App\Listeners\Auth;

use App\Events\Auth\UserRegistered;
use App\Services\Auth\PhoneVerificationService;

class SendVerificationSMS
{
    public function __construct(
        private PhoneVerificationService $verificationService
    ) {}

    public function handle(UserRegistered $event): void
    {
        $user = $event->user;
        
        $this->verificationService->sendSMS(
            $user->phone,
            $event->verificationCode
        );
    }
}
