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

    public function checkEmail(Request $request)
    {
        $secret = (string) config('services.internal_notifications.secret');
        $provided = (string) $request->header('X-Internal-Notification-Secret');

        if ($secret === '' || ! hash_equals($secret, $provided)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));

        $exists = \App\Models\User::whereRaw('LOWER(email) = ?', [$email])
            ->whereNotNull('password')
            ->exists();

        return response()->json([
            'success' => true,
            'exists' => $exists,
        ]);
    }

    public function fetchUserCredentials(Request $request)
    {
        $secret = (string) config('services.internal_notifications.secret');
        $provided = (string) $request->header('X-Internal-Notification-Secret');

        if ($secret === '' || ! hash_equals($secret, $provided)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));

        $user = \App\Models\User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'password' => $user->password,
                    'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->toIso8601String() : null,
                ],
            ],
        ]);
    }

    public function verifyUserId(Request $request)
    {
        $secret = (string) config('services.internal_notifications.secret');
        $provided = (string) $request->header('X-Internal-Notification-Secret');

        if ($secret === '' || ! hash_equals($secret, $provided)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = \App\Models\User::find($validated['user_id']);

        $valid = $user && strtolower(trim($user->email)) === $email;

        return response()->json([
            'success' => true,
            'valid' => $valid,
        ]);
    }
}
