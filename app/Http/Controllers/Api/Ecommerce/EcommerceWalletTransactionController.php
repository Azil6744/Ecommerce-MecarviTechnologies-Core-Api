<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EcommerceWalletTransactionController extends Controller
{
    public function summary(Request $request)
    {
        $token = $request->bearerToken();
        $centralUrl = rtrim(config('services.central_auth.url'), '/');
        
        try {
            if ($token) {
                $response = Http::acceptJson()
                    ->withToken($token)
                    ->timeout(5)
                    ->get($centralUrl . '/user/wallet');
            } else {
                $email = $request->user()->email;
                $secret = (string) config('services.internal_notifications.secret');
                $response = Http::acceptJson()
                    ->withHeaders(['X-Internal-Notification-Secret' => $secret])
                    ->timeout(5)
                    ->get($centralUrl . '/v1/internal/admin/wallet/' . urlencode($email));
            }

            if ($response->successful()) {
                $data = $response->json('data');
                if (isset($data['wallet'])) {
                    $balance = (float)($data['wallet']['balance'] ?? 0);
                    $transactions = collect($data['transactions'] ?? []);
                    $credits = (float) $transactions->filter(fn ($t) => in_array(strtolower($t['type'] ?? ''), ['credit', 'deposit', 'refund', 'affiliate earned', 'affiliate_earned']))->sum('amount');
                    $debits = (float) $transactions->filter(fn ($t) => !in_array(strtolower($t['type'] ?? ''), ['credit', 'deposit', 'refund', 'affiliate earned', 'affiliate_earned']))->sum('amount');
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'balance' => $balance,
                            'available_balance' => $balance,
                            'usable_balance' => $balance,
                            'credits' => $credits,
                            'debits' => $debits,
                            'transactions_count' => $transactions->count(),
                            'last_updated' => $data['wallet']['updated_at'] ?? null,
                        ],
                    ]);
                }
                return response()->json([
                    'success' => true,
                    'data' => $data,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Central wallet summary failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => 0.00,
                'available_balance' => 0.00,
                'usable_balance' => 0.00,
                'credits' => 0.00,
                'debits' => 0.00,
                'transactions_count' => 0,
                'last_updated' => null,
            ],
        ]);
    }

    public function index(Request $request)
    {
        $token = $request->bearerToken();
        $centralUrl = rtrim(config('services.central_auth.url'), '/');

        try {
            if ($token) {
                $response = Http::acceptJson()
                    ->withToken($token)
                    ->timeout(5)
                    ->get($centralUrl . '/user/wallet/transactions');
            } else {
                $email = $request->user()->email;
                $secret = (string) config('services.internal_notifications.secret');
                $response = Http::acceptJson()
                    ->withHeaders(['X-Internal-Notification-Secret' => $secret])
                    ->timeout(5)
                    ->get($centralUrl . '/v1/internal/admin/wallet/' . urlencode($email));
            }

            if ($response->successful()) {
                $data = $response->json('data');
                $txList = isset($data['transactions']) ? $data['transactions'] : $data;
                return response()->json([
                    'success' => true,
                    'data' => $txList,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Central wallet index failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'data' => []]);
    }

    public function store(Request $request)
    {
        $token = $request->bearerToken();
        $centralUrl = rtrim(config('services.central_auth.url'), '/');

        try {
            if ($token) {
                $client = Http::acceptJson()
                    ->withToken($token)
                    ->timeout(5);

                if ($request->hasHeader('X-Pin-Authorization')) {
                    $client = $client->withHeaders([
                        'X-Pin-Authorization' => $request->header('X-Pin-Authorization')
                    ]);
                }

                $response = $client->post($centralUrl . '/user/wallet/transaction', $request->all());
            } else {
                $email = $request->user()->email;
                $secret = (string) config('services.internal_notifications.secret');
                $response = Http::acceptJson()
                    ->withHeaders(['X-Internal-Notification-Secret' => $secret])
                    ->timeout(5)
                    ->post($centralUrl . '/v1/internal/admin/wallet/adjust', array_merge($request->all(), ['email' => $email]));
            }

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json('data'),
                ]);
            }
            return response()->json(
                $response->json() ?: [
                    'success' => false,
                    'message' => 'Failed to create transaction: ' . substr($response->body(), 0, 150),
                ],
                $response->status()
            );
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
        return response()->json(['success' => false, 'message' => 'Method not supported in centralized mode.'], 501);
    }

    public function destroy(Request $request, $id)
    {
        return response()->json(['success' => false, 'message' => 'Method not supported in centralized mode.'], 501);
    }
}
