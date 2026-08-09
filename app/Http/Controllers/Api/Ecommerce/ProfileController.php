<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Support\PasswordValidationRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Get user profile
     */
    public function show(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
            'username' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $payload = $request->only(['name', 'email', 'username', 'phone']);

        // Proxy update to Central Auth first
        $centralResponse = $this->proxyToCentralAuth($request, '/profile', 'PUT', $payload);

        if ($centralResponse && !$centralResponse->successful()) {
            return response()->json([
                'success' => false,
                'message' => $centralResponse->json('message') ?? 'Failed to update profile centrally.',
                'errors' => $centralResponse->json('errors') ?? [],
            ], $centralResponse->status());
        }

        $user = $request->user();
        $allowed = [];

        foreach ($payload as $key => $value) {
            if (Schema::hasColumn($user->getTable(), $key)) {
                $allowed[$key] = $value;
            }
        }

        $oldEmail = $user->email;
        $user->update($allowed);

        if ($user->email && strtolower($user->email) !== strtolower($oldEmail)) {
            try {
                app(\App\Services\EmailNotificationService::class)->sendEvent('change_email_confirmation', [
                    'customer_name' => $user->name,
                    'new_email' => $user->email,
                    'site_name' => config('app.name', 'Mecarvi Embroidery'),
                ], $user->email);
            } catch (\Throwable $e) {
                Log::warning('Failed to send change_email_confirmation email: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => PasswordValidationRules::rules(),
        ], PasswordValidationRules::messages());

        $payload = [
            'current_password' => $request->current_password,
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation,
        ];

        // Proxy update to Central Auth first
        $centralResponse = $this->proxyToCentralAuth($request, '/profile/password', 'PUT', $payload);

        if ($centralResponse && !$centralResponse->successful()) {
            return response()->json(
                $centralResponse->json() ?: [
                    'success' => false,
                    'message' => 'The current password is incorrect.',
                ],
                $centralResponse->status()
            );
        }

        $user = $request->user();
        $user->update(['password' => Hash::make($request->password)]);

        try {
            app(\App\Services\EmailNotificationService::class)->sendEvent('change_password_confirmation', [
                'customer_name' => $user->name,
                'site_name' => config('app.name', 'Mecarvi Embroidery'),
            ], $user->email);
        } catch (\Throwable $e) {
            Log::warning('Failed to send change_password_confirmation email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ]);
    }

    /**
     * Update user PIN
     */
    public function updatePin(Request $request)
    {
        $request->validate([
            'current_pin' => 'nullable|string|size:4|regex:/^\d+$/',
            'pin' => 'required|string|size:4|regex:/^\d+$/',
            'pin_confirmation' => 'required|same:pin',
        ]);

        $user = $request->user();
        if (!Schema::hasColumn($user->getTable(), 'pin')) {
            return response()->json([
                'success' => false,
                'message' => 'PIN support is not available on this server yet.',
            ], 422);
        }

        $payload = [
            'current_pin' => $request->current_pin,
            'pin' => $request->pin,
            'pin_confirmation' => $request->pin_confirmation,
        ];

        // Proxy update to Central Auth first
        $centralResponse = $this->proxyToCentralAuth($request, '/profile/pin', 'PUT', $payload);

        if ($centralResponse && !$centralResponse->successful()) {
            return response()->json(
                $centralResponse->json() ?: [
                    'success' => false,
                    'message' => 'The current PIN is incorrect.',
                ],
                $centralResponse->status()
            );
        }

        $user->update(['pin' => Hash::make($request->pin)]);

        try {
            $service = app(\App\Services\EmailNotificationService::class);
            $service->sendEvent('pin_verification', [
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'pin_code' => $request->pin,
                'expiry_minutes' => 30,
                'site_name' => config('app.name', 'Mecarvi Embroidery'),
            ], $user->email);

            $service->sendEvent('customer_credit_verification', [
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'verification_code' => $request->pin,
                'site_name' => config('app.name', 'Mecarvi Embroidery'),
            ], $user->email);
        } catch (\Throwable $e) {
            Log::warning('Failed to send pin verification email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'PIN updated successfully',
        ]);
    }

    /**
     * Determine candidate Central Auth API URLs.
     */
    private function centralAuthBaseUrls(): array
    {
        $candidates = [
            config('services.central_auth.internal_url'),
            config('services.central_auth.url'),
            config('services.central_auth.api_url'),
            'https://auth-api.mecarvi.com/api',
            'http://127.0.0.1:8001/api',
        ];

        return array_values(array_unique(array_filter(array_map(function (?string $url) {
            if (! $url) {
                return null;
            }

            $normalized = rtrim($url, '/');
            return str_ends_with($normalized, '/api') ? $normalized : $normalized . '/api';
        }, $candidates))));
    }

    /**
     * Proxy a request to Central Auth using the user's active session token.
     */
    private function proxyToCentralAuth(Request $request, string $endpoint, string $method, array $data): ?\Illuminate\Http\Client\Response
    {
        $token = $request->header('X-Central-Auth-Token') ?? $request->bearerToken();
        if (! $token) {
            return null;
        }

        foreach ($this->centralAuthBaseUrls() as $centralAuthUrl) {
            try {
                $headers = [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ];

                if ($request->hasHeader('X-Pin-Authorization')) {
                    $headers['X-Pin-Authorization'] = $request->header('X-Pin-Authorization');
                }

                $client = Http::withHeaders($headers)->timeout(10);

                $url = $centralAuthUrl . $endpoint;
                $response = null;

                if ($method === 'PUT') {
                    $response = $client->put($url, $data);
                } elseif ($method === 'POST') {
                    $response = $client->post($url, $data);
                } elseif ($method === 'PATCH') {
                    $response = $client->patch($url, $data);
                } else {
                    $response = $client->get($url, $data);
                }

                if ($response) {
                    return $response;
                }
            } catch (\Throwable $e) {
                Log::warning("ProfileController proxy failed for url {$centralAuthUrl}: " . $e->getMessage());
            }
        }

        return null;
    }
}
