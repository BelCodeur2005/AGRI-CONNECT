<?php

// app/Listeners/Auth/HandlePhoneVerification.php
namespace App\Listeners\Auth;

use App\Events\Auth\PhoneVerified;
use App\Services\Notifications\NotificationService;
use App\Enums\NotificationType;

class HandlePhoneVerification
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(PhoneVerified $event): void
    {
        $user = $event->user;

        // Notifier succès vérification
        $this->notificationService->send(
            $user,
            NotificationType::PHONE_VERIFIED,
            'Téléphone vérifié',
            'Votre numéro de téléphone a été vérifié avec succès !',
            ['user_id' => $user->id]
        );
    }
}
