<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
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

            $email = strtolower(trim((string) $centralUser['email']));
            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

            if (! $user) {
                \Log::info('CentralAuthTokenMiddleware: Creating new local user for ' . $email);
                $user = User::create([
                    'name' => $centralUser['name'] ?? 'User',
                    'email' => $email,
                    'username' => $this->availableUsername($centralUser['username'] ?? Str::before($email, '@')),
                    'password' => bcrypt(Str::random(16)),
                    'role' => $this->localRole($centralUser['role'] ?? null),
                ]);
            } else {
                $this->syncLocalUserProfile($user, $centralUser);
            }

            $this->authenticateRequestAs($request, $user);
            $this->linkSiteUserInCentralAuth($token, $user);
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

    private function syncLocalUserProfile(User $user, array $centralUser): void
    {
        $payload = [];

        foreach (['name', 'phone'] as $field) {
            if (Schema::hasColumn($user->getTable(), $field) && array_key_exists($field, $centralUser)) {
                $payload[$field] = $centralUser[$field];
            }
        }

        if (! empty($centralUser['username'])
            && Schema::hasColumn($user->getTable(), 'username')
            && $centralUser['username'] !== $user->username
            && ! User::where('username', $centralUser['username'])->whereKeyNot($user->id)->exists()
        ) {
            $payload['username'] = $centralUser['username'];
        }

        if ($payload) {
            $user->update($payload);
        }
    }

    private function availableUsername(string $username): string
    {
        $base = Str::slug($username, '_') ?: 'user';
        $candidate = $base;
        $suffix = 1;

        while (User::where('username', $candidate)->exists()) {
            $suffix++;
            $candidate = $base . '_' . $suffix;
        }

        return $candidate;
    }

    private function localRole(?string $centralRole): string
    {
        return in_array($centralRole, ['super_admin', 'admin', 'editor', 'customer', 'seller'], true)
            ? $centralRole
            : 'customer';
    }

    private function linkSiteUserInCentralAuth(string $token, User $user): void
    {
        $siteSlug = trim((string) config('services.mccarvy_site.slug', ''));
        if ($siteSlug === '') {
            return;
        }

        foreach ($this->centralAuthBaseUrls() as $centralAuthUrl) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'X-Central-Auth-Token' => $token,
                    'Accept' => 'application/json',
                ])->timeout(5)->post($centralAuthUrl . '/user/link-site', [
                    'site_slug' => $siteSlug,
                    'site_user_id' => $user->id,
                ]);

                if ($response->successful()) {
                    return;
                }
            } catch (\Throwable $e) {
                \Log::warning('CentralAuthTokenMiddleware: Failed linking site user', [
                    'site_slug' => $siteSlug,
                    'user_id' => $user->id,
                    'url' => $centralAuthUrl,
                    'error' => $e->getMessage(),
                ]);
            }
        }
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

                $validateResponse = $client->post($centralAuthUrl . '/auth/validate-token', [
                    'token' => $token,
                ]);
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
