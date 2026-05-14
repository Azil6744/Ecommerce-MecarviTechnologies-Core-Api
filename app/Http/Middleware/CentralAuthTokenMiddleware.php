<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;

class CentralAuthTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (! $token) {
            \Log::info('CentralAuthTokenMiddleware: No token found in request');
            return response()->json([
                'message' => 'Unauthenticated. Please provide a valid central auth token.',
            ], 401);
        }

        \Log::info('CentralAuthTokenMiddleware: Token found in request');

        try {
            $centralUser = $this->validateAgainstCentralAuth($token);

            if (! $centralUser) {
                return response()->json([
                    'message' => 'Unauthenticated. Central auth token is invalid or could not be verified.',
                ], 401);
            }

            \Log::info('CentralAuthTokenMiddleware: Token valid for ' . $centralUser['email']);

            // Find local user by email
            $user = User::where('email', $centralUser['email'])->first();

            if (! $user) {
                \Log::info('CentralAuthTokenMiddleware: Creating new local user for ' . $centralUser['email']);
                // Create local user if it doesn't exist
                $user = User::create([
                    'name' => $centralUser['name'],
                    'email' => $centralUser['email'],
                    'username' => $centralUser['username'] ?? Str::before($centralUser['email'], '@'),
                    'password' => bcrypt(Str::random(16)),
                    'role' => 'viewer',
                ]);
            }

            Auth::setUser($user);
            Auth::guard('sanctum')->setUser($user);
            \Log::info('CentralAuthTokenMiddleware: User authenticated for request');

            $request->setUserResolver(function () use ($user) {
                return $user;
            });
        } catch (\Exception $e) {
            \Log::error('CentralAuthTokenMiddleware: Exception during validation: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to validate central auth token.',
            ], 401);
        }

        return $next($request);
    }

    private function validateAgainstCentralAuth(string $token): ?array
    {
        foreach ($this->centralAuthBaseUrls() as $centralAuthUrl) {
            \Log::info('CentralAuthTokenMiddleware: Validating token against ' . $centralAuthUrl);

            try {
                $client = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ])->timeout(10);

                $validateResponse = $client->post($centralAuthUrl . '/auth/validate-token');
                $validatedUser = $this->extractValidatedUser($validateResponse);
                if ($validatedUser) {
                    return $validatedUser;
                }

                $meResponse = $client->get($centralAuthUrl . '/auth/me');
                $meUser = $this->extractMeUser($meResponse);
                if ($meUser) {
                    return $meUser;
                }

                \Log::warning('CentralAuthTokenMiddleware: Central auth rejected token', [
                    'url' => $centralAuthUrl,
                    'validate_status' => $validateResponse->status(),
                    'validate_body' => $validateResponse->json(),
                    'me_status' => $meResponse->status(),
                    'me_body' => $meResponse->json(),
                ]);
            } catch (\Throwable $e) {
                \Log::warning('CentralAuthTokenMiddleware: Central auth request failed', [
                    'url' => $centralAuthUrl,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    private function centralAuthBaseUrls(): array
    {
        $candidates = [
            env('CENTRAL_AUTH_INTERNAL_URL'),
            env('CENTRAL_AUTH_URL'),
            env('CENTRAL_AUTH_API_URL'),
            'https://mecarvi.com/auth-api/api',
            'http://127.0.0.1:8001/api',
        ];

        return array_values(array_unique(array_filter(array_map(function (?string $url) {
            if (! $url) {
                return null;
            }

            return rtrim($url, '/');
        }, $candidates))));
    }

    private function extractValidatedUser(Response $response): ?array
    {
        if (! $response->successful() || ! $response->json('valid')) {
            return null;
        }

        $payload = $response->json();
        if (empty($payload['email'])) {
            return null;
        }

        return $payload;
    }

    private function extractMeUser(Response $response): ?array
    {
        if (! $response->successful()) {
            return null;
        }

        $payload = $response->json();
        if (empty($payload['email'])) {
            return null;
        }

        return $payload;
    }
}
