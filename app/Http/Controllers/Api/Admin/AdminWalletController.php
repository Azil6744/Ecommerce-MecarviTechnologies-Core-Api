<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminWalletController extends Controller
{
    private function centralCall(string $method, string $path, array $data = [])
    {
        $centralUrl = rtrim(config('services.central_auth.url'), '/');
        $secret = (string) config('services.internal_notifications.secret');

        $request = Http::acceptJson()
            ->withHeaders(['X-Internal-Notification-Secret' => $secret])
            ->timeout(5);

        if (strtolower($method) === 'post') {
            return $request->post($centralUrl . $path, $data);
        }

        return $request->get($centralUrl . $path, $data);
    }

    public function index(Request $request)
    {
        try {
            $response = $this->centralCall('get', '/v1/internal/admin/wallet-transactions');
            if ($response->successful()) {
                return response()->json([
                    'data' => $response->json('data'),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Central admin index failed: ' . $e->getMessage());
        }

        return response()->json(['data' => []]);
    }

    public function getUserWallet(User $user)
    {
        try {
            $response = $this->centralCall('get', '/v1/internal/admin/wallet/' . urlencode($user->email));
            if ($response->successful()) {
                $data = $response->json('data');
                return response()->json([
                    'user_id' => $user->id,
                    'balance' => (float)($data['wallet']['balance'] ?? 0),
                    'transactions' => $data['transactions'] ?? [],
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Central admin getUserWallet failed: ' . $e->getMessage());
        }

        return response()->json([
            'user_id' => $user->id,
            'balance' => 0.00,
            'transactions' => [],
        ]);
    }

    public function creditWallet(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string',
        ]);

        try {
            $response = $this->centralCall('post', '/v1/internal/admin/wallet/adjust', [
                'email' => $user->email,
                'type' => 'credit',
                'amount' => $request->amount,
                'description' => $request->reason,
            ]);

            if ($response->successful()) {
                $tx = $response->json('data');
                $newBal = (float)($tx['balance_after'] ?? 0);

                try {
                    $emailService = app(\App\Services\EmailNotificationService::class);
                    $payload = [
                        'customer_name' => $user->name,
                        'customer_email' => $user->email,
                        'amount' => '$' . number_format((float)$request->amount, 2),
                        'new_balance' => '$' . number_format($newBal, 2),
                        'site_name' => config('app.name', 'Mecarvi Embroidery'),
                    ];
                    $emailService->sendEvent('customer_add_balance', $payload, $user->email);
                    $emailService->sendEvent('wallet_deposit', $payload, $user->email);
                } catch (\Throwable $e) {
                    Log::warning('AdminWalletController credit email failed: ' . $e->getMessage());
                }

                return response()->json([
                    'message' => 'Wallet credited successfully',
                    'balance' => $newBal,
                ]);
            }

            return response()->json([
                'message' => $response->json('message') ?: 'Failed to credit wallet',
            ], $response->status());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function debitWallet(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string',
        ]);

        try {
            $response = $this->centralCall('post', '/v1/internal/admin/wallet/adjust', [
                'email' => $user->email,
                'type' => 'debit',
                'amount' => $request->amount,
                'description' => $request->reason,
            ]);

            if ($response->successful()) {
                $tx = $response->json('data');
                $newBal = (float)($tx['balance_after'] ?? 0);

                try {
                    $emailService = app(\App\Services\EmailNotificationService::class);
                    $payload = [
                        'customer_name' => $user->name,
                        'customer_email' => $user->email,
                        'amount' => '$' . number_format((float)$request->amount, 2),
                        'new_balance' => '$' . number_format($newBal, 2),
                        'site_name' => config('app.name', 'Mecarvi Embroidery'),
                    ];
                    $emailService->sendEvent('customer_sub_balance', $payload, $user->email);
                } catch (\Throwable $e) {
                    Log::warning('AdminWalletController debit email failed: ' . $e->getMessage());
                }

                return response()->json([
                    'message' => 'Amount deducted from wallet',
                    'balance' => $newBal,
                ]);
            }

            return response()->json([
                'message' => $response->json('message') ?: 'Failed to debit wallet',
            ], $response->status());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}