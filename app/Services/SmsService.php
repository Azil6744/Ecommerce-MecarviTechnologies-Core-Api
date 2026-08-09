<?php

namespace App\Services;

use App\Models\SmsSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function sendSms(string $to, string $message, ?string &$errorMessage = null): bool
    {
        $settings = SmsSetting::first();

        $sid = $settings?->twilio_sid ?: config('services.twilio.sid', env('TWILIO_SID'));
        $token = $settings?->twilio_auth_token ?: config('services.twilio.token', env('TWILIO_AUTH_TOKEN'));
        $from = $settings?->twilio_from_number ?: config('services.twilio.from', env('TWILIO_FROM'));
        $provider = $settings?->provider ?: 'twilio';

        $isEnabled = $settings ? (bool) $settings->is_enabled : (! empty($sid) && ! empty($token));

        if (! $isEnabled) {
            $errorMessage = "SMS sending skipped: SMS system is disabled.";
            Log::info($errorMessage);
            return false;
        }

        if ($provider === 'twilio') {
            return $this->sendTwilioWithCreds($sid, $token, $from, $to, $message, $errorMessage);
        } elseif ($provider === 'infobip') {
            return $this->sendInfobipWithCreds($settings?->infobip_api_key, $settings?->infobip_base_url, $to, $message, $errorMessage);
        }

        $errorMessage = "SMS sending failed: Unsupported provider {$provider}";
        Log::error($errorMessage);
        return false;
    }

    private function sendTwilioWithCreds(?string $sid, ?string $token, ?string $from, string $to, string $message, ?string &$errorMessage = null): bool
    {
        if (! $sid || ! $token || ! $from) {
            $errorMessage = "SMS sending failed: Twilio SID, Auth Token, or From Number missing.";
            Log::error($errorMessage);
            return false;
        }

        try {
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
            $response = Http::asForm()
                ->withBasicAuth($sid, $token)
                ->post($url, [
                    'To' => $to,
                    'From' => $from,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                Log::info("SMS sent via Twilio to {$to}");
                return true;
            }

            $body = $response->json();
            $errorMessage = $body['message'] ?? $response->body();
            Log::error("Twilio SMS send error: " . $errorMessage);
            return false;
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            Log::error("Twilio SMS sending exception: " . $errorMessage);
            return false;
        }
    }

    private function sendInfobipWithCreds(?string $apiKey, ?string $baseUrl, string $to, string $message, ?string &$errorMessage = null): bool
    {
        $baseUrl = rtrim($baseUrl ?? '', '/');

        if (! $apiKey || ! $baseUrl) {
            $errorMessage = "SMS sending failed: Infobip credentials missing.";
            Log::error($errorMessage);
            return false;
        }

        try {
            $url = "{$baseUrl}/sms/2/text/advanced";
            $response = Http::withHeaders([
                'Authorization' => "App {$apiKey}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($url, [
                'messages' => [
                    [
                        'destinations' => [
                            ['to' => $to],
                        ],
                        'text' => $message,
                    ],
                ],
            ]);

            if ($response->successful()) {
                Log::info("SMS sent via Infobip to {$to}");
                return true;
            }

            $errorMessage = $response->body();
            Log::error("Infobip SMS send error: " . $errorMessage);
            return false;
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            Log::error("Infobip SMS sending exception: " . $errorMessage);
            return false;
        }
    }
}
