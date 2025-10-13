<?php

// app/Services/Notifications/PushNotificationService.php
namespace App\Services\Notifications;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    public function send(string $fcmToken, string $title, string $body, array $data = []): void
    {
        try {
            Http::withHeaders([
                'Authorization' => 'key=' . config('services.fcm.server_key'),
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'badge' => 1,
                ],
                'data' => $data,
                'priority' => 'high',
            ]);

        } catch (\Exception $e) {
            Log::error('Push Notification failed', ['error' => $e->getMessage()]);
        }
    }
}