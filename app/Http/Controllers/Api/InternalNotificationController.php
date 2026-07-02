<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;

class InternalNotificationController extends Controller
{
    public function __construct(private readonly EmailNotificationService $emails) {}

    public function userRegistered(Request $request)
    {
        $secret = (string) config('services.internal_notifications.secret');
        $provided = (string) $request->header('X-Internal-Notification-Secret');

        if ($secret === '' || ! hash_equals($secret, $provided)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'site_name' => ['nullable', 'string', 'max:255'],
        ]);

        $logs = $this->emails->sendEvent('user_registered', [
            'customer_name' => $validated['name'],
            'customer_email' => $validated['email'],
            'site_name' => $validated['site_name'] ?? config('app.name', 'Mecarvi Embroidery'),
        ], $validated['email']);

        return response()->json([
            'success' => true,
            'data' => ['logs' => $logs],
        ]);
    }
}
