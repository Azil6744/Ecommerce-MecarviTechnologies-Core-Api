<?php

namespace App\Services;

use App\Models\SmsSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function sendSms(string $to, string $message): bool
    {
        $settings = SmsSetting::first();

        if (!$settings || !$settings->is_enabled) {
            Log::info("SMS sending skipped: SMS system is disabled.");
            return false;
        }

        if ($settings->provider === 'twilio') {
            return $this->sendTwilio($settings, $to, $message);
        } elseif ($settings->provider === 'infobip') {
            return $this->sendInfobip($settings, $to, $message);
        }

        Log::error("SMS sending failed: Unsupported provider {$settings->provider}");
        return false;
    }

    private function sendTwilio(SmsSetting $settings, string $to, string $message): bool
    {
        $sid = $settings->twilio_sid;
        $token = $settings->twilio_auth_token;
        $from = $settings->twilio_from_number;

        if (!$sid || !$token || !$from) {
            Log::error("SMS sending failed: Twilio credentials are not fully configured.");
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

            Log::error("Twilio SMS send error: " . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error("Twilio SMS sending exception: " . $e->getMessage());
            return false;
        }
    }

    private function sendInfobip(SmsSetting $settings, string $to, string $message): bool
    {
        $apiKey = $settings->infobip_api_key;
        $baseUrl = rtrim($settings->infobip_base_url, '/');

        if (!$apiKey || !$baseUrl) {
            Log::error("SMS sending failed: Infobip credentials are not fully configured.");
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
                            ['to' => $to]
                        ],
                        'text' => $message
                    ]
                ]
            ]);

            if ($response->successful()) {
                Log::info("SMS sent via Infobip to {$to}");
                return true;
            }

            Log::error("Infobip SMS send error: " . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error("Infobip SMS sending exception: " . $e->getMessage());
            return false;
        }
    }
}
