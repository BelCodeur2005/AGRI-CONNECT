<?php

// app/Services/Notifications/EmailService.php
namespace App\Services\Notifications;

use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function send(string $email, string $subject, string $body): void
    {
        // TODO: Implémenter envoi email avec template
        // Mail::to($email)->send(new NotificationMail($subject, $body));
    }
}