<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EcommerceMembershipController extends Controller
{
    private function centralCall(string $method, string $path, array $data = [], ?string $token = null)
    {
        $centralUrl = rtrim(config('services.central_auth.url'), '/');
        
        if ($token) {
            $request = Http::acceptJson()
                ->withToken($token)
                ->timeout(5);
        } else {
            $secret = (string) config('services.internal_notifications.secret');
            $request = Http::acceptJson()
                ->withHeaders(['X-Internal-Notification-Secret' => $secret])
                ->timeout(5);
        }

        if (strtolower($method) === 'post') {
            return $request->post($centralUrl . $path, $data);
        }

        return $request->get($centralUrl . $path, $data);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $token = $request->bearerToken();

        // If admin / superadmin (or no token), call admin endpoint to list all
        if ($user && $user->isSuperAdmin() && !$token) {
            try {
                $response = $this->centralCall('get', '/v1/internal/admin/memberships');
                if ($response->successful()) {
                    return response()->json([
                        'success' => true,
                        'data' => $response->json('data'),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Central memberships admin index failed: ' . $e->getMessage());
            }
            return response()->json(['success' => true, 'data' => []]);
        }

        // Customer query
        try {
            if ($token) {
                $response = $this->centralCall('get', '/user/memberships', [], $token);
            } else {
                $response = $this->centralCall('get', '/v1/internal/admin/memberships');
                if ($response->successful()) {
                    $filtered = collect($response->json('data'))->filter(fn($m) => strtolower($m['user']['email'] ?? '') === strtolower($user->email))->values();
                    return response()->json([
                        'success' => true,
                        'data' => $filtered,
                    ]);
                }
            }

            if ($response && $response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json('data'),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Central memberships index failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'data' => []]);
    }

    public function store(Request $request)
    {
        $token = $request->bearerToken();
        $user = $request->user();

        try {
            if ($token) {
                $response = $this->centralCall('post', '/user/memberships', $request->all(), $token);
            } else {
                $response = $this->centralCall('post', '/v1/internal/admin/memberships/update', array_merge($request->all(), ['email' => $user->email]));
            }

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json('data'),
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => $response->json('message') ?: 'Failed to store membership',
            ], $response->status());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        return response()->json(['success' => false, 'message' => 'Method not supported in centralized mode.'], 501);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        try {
            $response = $this->centralCall('post', '/v1/internal/admin/memberships/update', array_merge($request->all(), ['email' => $user->email]));
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json('data'),
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => $response->json('message') ?: 'Failed to update membership',
            ], $response->status());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        return response()->json(['success' => false, 'message' => 'Method not supported in centralized mode.'], 501);
    }
}
