<?php

namespace App\Services;

use App\Models\User;
use App\Models\EcommerceWalletTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WalletService
{
    /**
     * Adjust user's wallet balance centrally and log locally.
     *
     * @param int $userId
     * @param float $amount Absolute value of the amount
     * @param string $type Transaction type ('credit', 'debit', 'refund', 'affiliate_earned', 'affiliate_deduction', etc.)
     * @param string $description
     * @param string|null $referenceId
     * @return bool
     */
    public static function adjustWallet(int $userId, float $amount, string $type, string $description, ?string $referenceId = null): bool
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                Log::warning("WalletService: User ID {$userId} not found.");
                return false;
            }

            $centralUrl = rtrim(config('services.central_auth.url'), '/');
            $secret = (string) config('services.internal_notifications.secret');

            // Determine if it is a credit or debit for the Central API
            // Central API's adjust endpoint expects type = 'credit' (adds to balance) or 'debit' (deducts)
            $typeLower = strtolower($type);
            $isCredit = in_array($typeLower, ['credit', 'deposit', 'refund', 'affiliate earned', 'affiliate_earned', 'affiliate credit', 'affiliate_credit', 'refund credit', 'refund_credit']);
            $centralType = $isCredit ? 'credit' : 'debit';

            $response = Http::acceptJson()
                ->withHeaders(['X-Internal-Notification-Secret' => $secret])
                ->timeout(5)
                ->post($centralUrl . '/v1/internal/admin/wallet/adjust', [
                    'email' => $user->email,
                    'type' => $centralType,
                    'amount' => abs($amount),
                    'description' => $description,
                    'reference_id' => $referenceId,
                ]);

            if ($response->successful()) {
                $tx = $response->json('data');
                $newBalance = (float) ($tx['balance_after'] ?? 0.00);

                // Update local user's wallet_balance
                $user->wallet_balance = $newBalance;
                $user->save();

                // Log local transaction
                EcommerceWalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'amount' => $isCredit ? abs($amount) : -abs($amount),
                    'balance_after' => $newBalance,
                    'description' => $description,
                    'status' => 'Completed',
                    'reference_id' => $referenceId,
                ]);

                return true;
            } else {
                Log::error("WalletService: Central API adjustment failed. Status: " . $response->status() . " Body: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("WalletService: Exception in adjustWallet: " . $e->getMessage());
            return false;
        }
    }
}
