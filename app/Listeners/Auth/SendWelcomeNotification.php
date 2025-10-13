<?php

// app/Listeners/Auth/SendWelcomeNotification.php
namespace App\Listeners\Auth;

use App\Events\Auth\UserRegistered;
use App\Services\Notifications\NotificationService;
use App\Enums\NotificationType;

class SendWelcomeNotification
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(UserRegistered $event): void
    {
        $user = $event->user;

        $this->notificationService->send(
            $user,
            NotificationType::WELCOME,
            'Bienvenue sur Agri-Connect',
            "Bonjour {$user->name}, bienvenue sur Agri-Connect ! Votre compte a été créé avec succès.",
            ['user_id' => $user->id]
        );
    }
}
