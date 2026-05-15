<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

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
        $token = $this->extractBearerToken($request);

        if (! $token) {
            \Log::info('CentralAuthTokenMiddleware: No token found in request', [
                'authorization_header_present' => $request->headers->has('Authorization'),
                'x_central_auth_token_present' => $request->headers->has('X-Central-Auth-Token'),
                'http_authorization' => $request->server('HTTP_AUTHORIZATION') ? 'present' : 'missing',
                'redirect_http_authorization' => $request->server('REDIRECT_HTTP_AUTHORIZATION') ? 'present' : 'missing',
            ]);
            return response()->json([
                'message' => 'Unauthenticated. Please provide a valid central auth token.',
            ], 401);
        }

        \Log::info('CentralAuthTokenMiddleware: Token found in request');

        try {
            $centralUser = $this->validateAgainstCentralAuth($token);

            if (! $centralUser) {
                $localUser = $this->validateAgainstLocalSanctum($token);
                if ($localUser) {
                    $this->authenticateRequestAs($request, $localUser);
                    \Log::info('CentralAuthTokenMiddleware: Token valid as local Sanctum token for ' . $localUser->email);

                    return $next($request);
                }

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

            $this->authenticateRequestAs($request, $user);
            \Log::info('CentralAuthTokenMiddleware: User authenticated for request');
        } catch (\Exception $e) {
            \Log::error('CentralAuthTokenMiddleware: Exception during validation: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to validate central auth token.',
            ], 401);
        }

        return $next($request);
    }

    private function authenticateRequestAs(Request $request, User $user): void
    {
        Auth::setUser($user);
        Auth::guard('sanctum')->setUser($user);

        $request->setUserResolver(function () use ($user) {
            return $user;
        });
    }

    private function extractBearerToken(Request $request): ?string
    {
        $candidates = [
            $request->bearerToken(),
            $this->parseBearerValue($request->header('Authorization')),
            $this->parseBearerValue($request->server('HTTP_AUTHORIZATION')),
            $this->parseBearerValue($request->server('REDIRECT_HTTP_AUTHORIZATION')),
            $this->parseBearerValue($request->server('Authorization')),
            $request->header('X-Central-Auth-Token'),
            $request->header('X-Auth-Token'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return null;
    }

    private function validateAgainstLocalSanctum(string $token): ?User
    {
        $accessToken = PersonalAccessToken::findToken($token);

        if (! $accessToken || ! $accessToken->tokenable instanceof User) {
            return null;
        }

        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return null;
        }

        return $accessToken->tokenable;
    }

    private function parseBearerValue(?string $headerValue): ?string
    {
        if (! $headerValue) {
            return null;
        }

        if (preg_match('/Bearer\s+(.+)/i', $headerValue, $matches) !== 1) {
            return null;
        }

        $token = trim($matches[1]);
        return $token !== '' ? $token : null;
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

            $normalized = rtrim($url, '/');
            return str_ends_with($normalized, '/api') ? $normalized : $normalized . '/api';
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
