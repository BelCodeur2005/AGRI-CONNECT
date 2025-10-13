<?php

// app/Services/Notifications/SmsService.php
namespace App\Services\Notifications;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $phone, string $message): void
    {
        try {
            $response = Http::withHeaders([
                'apiKey' => config('services.africastalking.api_key'),
            ])->asForm()->post('https://api.africastalking.com/version1/messaging', [
                'username' => config('services.africastalking.username'),
                'to' => $this->formatPhone($phone),
                'message' => $this->truncate($message, 160),
                'from' => config('services.africastalking.from'),
            ]);

            if (!$response->successful()) {
                Log::error('SMS failed', ['phone' => $phone, 'response' => $response->body()]);
            }

        } catch (\Exception $e) {
            Log::error('SMS Exception', ['phone' => $phone, 'error' => $e->getMessage()]);
        }
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (!str_starts_with($phone, '237')) {
            $phone = '237' . $phone;
        }
        return '+' . $phone;
    }

    private function truncate(string $message, int $length): string
    {
        return strlen($message) > $length ? substr($message, 0, $length - 3) . '...' : $message;
    }
}
