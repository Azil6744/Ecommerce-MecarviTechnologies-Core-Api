<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnsurePinAuthorization
{
    public function handle(Request $request, Closure $next, string $category = 'sensitive_action')
    {
        // Bypass PIN verification for wallet deposits (credits)
        if (($request->is('*/wallet-transactions') || $request->is('*/user/wallet/transaction')) && in_array(strtolower($request->input('type', '')), ['credit', 'deposit', 'refund', 'affiliate earned', 'affiliate_earned'])) {
            return $next($request);
        }

        $token = $request->header('X-Pin-Authorization')
            ?: $request->input('pin_authorization_token');

        if (! is_string($token) || trim($token) === '') {
            if (config('app.env') === 'local' || config('app.env') === 'testing') {
                Log::info('Bypassing PIN verification in local/testing environment.');
                return $next($request);
            }
            return $this->reject('PIN verification is required for this action.');
        }

        $payload = $this->verifiedPayload(trim($token));
        if (! $payload) {
            return $this->reject('PIN verification is invalid or expired.');
        }

        if (! $this->matchesUser($request, $payload)) {
            Log::warning('PIN authorization user mismatch', [
                'local_user_id' => $request->user()?->id,
                'central_user_id' => $request->attributes->get('central_auth_user')['id'] ?? null,
            ]);

            return $this->reject('PIN verification does not match this account.');
        }

        if (! $this->matchesCategory($payload, $category)) {
            return $this->reject('PIN verification is not valid for this action.');
        }

        $request->attributes->set('pin_authorization', $payload);

        return $next($request);
    }

    private function verifiedPayload(string $token): ?array
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$encodedPayload, $signature] = $parts;
        $expected = hash_hmac('sha256', $encodedPayload, $this->secret());

        if (! hash_equals($expected, $signature)) {
            return null;
        }

        $base64Payload = strtr($encodedPayload, '-_', '+/');
        $base64Payload .= str_repeat('=', (4 - strlen($base64Payload) % 4) % 4);
        $json = base64_decode($base64Payload, true);
        if (! is_string($json)) {
            return null;
        }

        $payload = json_decode($json, true);
        if (! is_array($payload)) {
            return null;
        }

        if ((int) ($payload['expires_at'] ?? 0) < now()->timestamp) {
            return null;
        }

        return $payload;
    }

    private function matchesUser(Request $request, array $payload): bool
    {
        $centralUser = $request->attributes->get('central_auth_user');
        $centralId = is_array($centralUser) ? ($centralUser['id'] ?? null) : null;

        if ($centralId !== null) {
            return (string) $centralId === (string) ($payload['user_id'] ?? '');
        }

        return (string) $request->user()?->id === (string) ($payload['user_id'] ?? '');
    }

    private function matchesCategory(array $payload, string $category): bool
    {
        $allowed = [
            'sensitive_action',
            $category,
            str_replace('.', '_', $category),
        ];

        return in_array((string) ($payload['action_category'] ?? ''), $allowed, true)
            || in_array((string) ($payload['action'] ?? ''), $allowed, true);
    }

    private function reject(string $message)
    {
        return response()->json([
            'message' => $message,
            'requires_pin' => true,
        ], 403);
    }

    private function secret(): string
    {
        return (string) config('services.pin_authorization.secret', config('app.key'));
    }
}
