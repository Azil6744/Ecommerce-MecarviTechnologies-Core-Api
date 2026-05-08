<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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

        if ($token) {
            \Log::info('CentralAuthTokenMiddleware: Token found in request');
            try {
                // Validate token with Central Auth API
                $centralAuthUrl = env('CENTRAL_AUTH_INTERNAL_URL', 'http://127.0.0.1:8001/api');
                \Log::info('CentralAuthTokenMiddleware: Validating token against ' . $centralAuthUrl);
                
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ])->post($centralAuthUrl . '/auth/validate-token');

                if ($response->successful() && $response->json('valid')) {
                    $centralUser = $response->json();
                    \Log::info('CentralAuthTokenMiddleware: Token valid for ' . $centralUser['email']);
                    
                    // Find local user by email
                    $user = User::where('email', $centralUser['email'])->first();
                    
                    if (!$user) {
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

                    // Log the user in for the current request on the sanctum guard
                    Auth::guard('sanctum')->setUser($user);
                    \Log::info('CentralAuthTokenMiddleware: User authenticated on sanctum guard');
                    
                    // Also set it as the default user for the request
                    $request->setUserResolver(function () use ($user) {
                        return $user;
                    });
                } else {
                    \Log::warning('CentralAuthTokenMiddleware: Token validation failed or invalid response', [
                        'status' => $response->status(),
                        'body' => $response->json()
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('CentralAuthTokenMiddleware: Exception during validation: ' . $e->getMessage());
            }
        } else {
            \Log::info('CentralAuthTokenMiddleware: No token found in request');
        }

        return $next($request);
    }
}
