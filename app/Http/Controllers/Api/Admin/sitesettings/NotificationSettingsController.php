<?php

namespace App\Http\Controllers\Api\Admin\sitesettings;

use App\Http\Controllers\Controller;
use App\Models\SmsSetting;
use App\Models\PushNotificationSetting;
use Illuminate\Http\Request;

class NotificationSettingsController extends Controller
{
    public function getSmsSettings(Request $request)
    {
        if (! $request->user()?->hasAdminAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $settings = SmsSetting::firstOrCreate([], [
            'is_enabled' => false,
            'provider' => 'twilio',
        ]);

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    public function saveSmsSettings(Request $request)
    {
        if (! $request->user()?->hasAdminAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'is_enabled' => 'required|boolean',
            'provider' => 'required|string',
            'twilio_sid' => 'nullable|string',
            'twilio_auth_token' => 'nullable|string',
            'twilio_from_number' => 'nullable|string',
            'infobip_api_key' => 'nullable|string',
            'infobip_base_url' => 'nullable|string',
        ]);

        $settings = SmsSetting::first();
        if (! $settings) {
            $settings = new SmsSetting;
        }

        $settings->fill($validated);
        $settings->save();

        return response()->json([
            'success' => true,
            'data' => $settings,
            'message' => 'SMS settings saved successfully.',
        ]);
    }

    public function getPushSettings(Request $request)
    {
        if (! $request->user()?->hasAdminAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $settings = PushNotificationSetting::firstOrCreate([], [
            'is_enabled' => false,
        ]);

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    public function savePushSettings(Request $request)
    {
        if (! $request->user()?->hasAdminAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'is_enabled' => 'required|boolean',
            'firebase_project_id' => 'nullable|string',
            'firebase_api_key' => 'nullable|string',
            'firebase_auth_domain' => 'nullable|string',
            'firebase_storage_bucket' => 'nullable|string',
            'firebase_messaging_sender_id' => 'nullable|string',
            'firebase_app_id' => 'nullable|string',
            'firebase_measurement_id' => 'nullable|string',
            'firebase_private_key_json' => 'nullable|string',
        ]);

        $settings = PushNotificationSetting::first();
        if (! $settings) {
            $settings = new PushNotificationSetting;
        }

        $settings->fill($validated);
        $settings->save();

        return response()->json([
            'success' => true,
            'data' => $settings,
            'message' => 'Push notification settings saved successfully.',
        ]);
    }
}
