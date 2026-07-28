<?php

namespace App\Services;

use App\Models\User;
use App\Models\SiteSetting;
use App\Models\EcommerceLoyaltyTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LoyaltyService
{
    /**
     * Adjust customer loyalty points locally and sync with Central Auth API.
     *
     * @param int $userId
     * @param int $points Absolute value of points to adjust (Central API handles the sign based on type)
     * @param string $type Transaction type (earned, redeemed, reversed, expired, manual_added, manual_removed, bonus, review_reward, referral)
     * @param string $reason Brief explanation of transaction
     * @param int|null $orderId Connected order ID, if applicable
     * @param string $status Local status of transaction (pending, available, redeemed, reversed, expired)
     * @return bool Success status
     */
    public static function adjustPoints(int $userId, int $points, string $type, string $reason, ?int $orderId = null, string $status = 'available'): bool
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                Log::warning("LoyaltyService: User ID {$userId} not found.");
                return false;
            }

            $settings = SiteSetting::first();
            $ratio = 0.01;
            if ($settings && $settings->loyalty_settings) {
                $loyalty = json_decode($settings->loyalty_settings, true);
                $ratio = (float)($loyalty['points_to_dollar_ratio'] ?? 0.01);
            }

            $centralUrl = rtrim(config('services.central_auth.url'), '/');
            $secret = (string) config('services.internal_notifications.secret');

            // 1. Sync with Central Auth API using internal secure endpoint
            $response = Http::acceptJson()
                ->withHeaders(['X-Internal-Notification-Secret' => $secret])
                ->timeout(5)
                ->post($centralUrl . '/v1/internal/admin/loyalty/adjust', [
                    'email' => $user->email,
                    'points' => abs($points),
                    'transaction_type' => $type,
                    'reason' => $reason,
                ]);

            if ($response->successful()) {
                $totalPoints = (int) ($response->json('total_points') ?? 0);

                // 2. Update local user points balance
                $user->loyalty_points = $totalPoints;
                $user->save();

                // Determine points sign for local transaction log
                $localPoints = abs($points);
                if (in_array(strtolower($type), ['manual_removed', 'redeemed', 'expired', 'reversed'])) {
                    $localPoints = -$localPoints;
                }

                // 3. Create local transaction log
                EcommerceLoyaltyTransaction::create([
                    'user_id' => $user->id,
                    'order_id' => $orderId,
                    'transaction_type' => $type,
                    'points' => $localPoints,
                    'dollar_value' => abs($points) * $ratio,
                    'status' => $status,
                    'reason' => $reason,
                ]);

                return true;
            } else {
                Log::error("LoyaltyService: Failed to sync points with Central API. Status: " . $response->status() . " Body: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("LoyaltyService: Exception in adjustPoints: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Award loyalty points for a gift card purchase if enabled in settings.
     *
     * @param int|null $userId
     * @param float $amount
     * @param int $orderId
     * @param string $orderNumber
     * @param string $status
     * @return bool
     */
    public static function awardPointsForGiftCard(?int $userId, float $amount, int $orderId, string $orderNumber, string $status = 'pending'): bool
    {
        try {
            if (!$userId) {
                return false;
            }

            $user = User::find($userId);
            if (!$user) {
                return false;
            }

            $settings = SiteSetting::first();
            $enabled = false;
            $earnOnGiftCards = false;
            $ratio = 0.01;
            
            if ($settings && $settings->loyalty_settings) {
                $loyalty = json_decode($settings->loyalty_settings, true);
                $enabled = filter_var($loyalty['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $earnOnGiftCards = filter_var($loyalty['earn_points_on_gift_cards'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $ratio = (float)($loyalty['points_to_dollar_ratio'] ?? 0.01);
            }

            if (!$enabled || !$earnOnGiftCards) {
                return false;
            }

            $earnPerUnitPrice = $settings && $settings->loyalty_points_earned_per_unit_price > 0 ? (float)$settings->loyalty_points_earned_per_unit_price : 50.00;
            $earnPoints = $settings ? (int)$settings->loyalty_points_earned_points : 2;
            
            $pointsEarned = (int)(floor($amount / $earnPerUnitPrice) * $earnPoints);
            
            if ($pointsEarned > 0) {
                if ($status === 'available') {
                    return self::adjustPoints(
                        $userId,
                        $pointsEarned,
                        'earned',
                        "Points earned for Gift Card Order #{$orderNumber}",
                        null,
                        'available'
                    );
                } else {
                    EcommerceLoyaltyTransaction::create([
                        'user_id' => $userId,
                        'order_id' => null,
                        'transaction_type' => 'earned',
                        'points' => $pointsEarned,
                        'dollar_value' => $pointsEarned * $ratio,
                        'status' => 'pending',
                        'reason' => "Points pending for Gift Card Order #{$orderNumber}",
                        'reference_type' => 'gift_card_order',
                        'reference_id' => (string) $orderId,
                        'reference_date' => now()->toDateString(),
                    ]);
                    return true;
                }
            }
            
            return false;
        } catch (\Throwable $e) {
            Log::error("LoyaltyService: Exception in awardPointsForGiftCard: " . $e->getMessage());
            return false;
        }
    }
}

