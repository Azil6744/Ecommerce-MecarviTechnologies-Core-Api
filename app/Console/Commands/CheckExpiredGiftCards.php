<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EcommerceGiftCard;
use App\Support\GiftCardMailer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckExpiredGiftCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ecommerce:check-expired-gift-cards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for expired gift cards, locks their balance, and sends notification emails.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting expired gift cards check...');

        $expiredCards = EcommerceGiftCard::whereIn(DB::raw('LOWER(status)'), ['active', 'delivered', 'partially used', 'partially_used', 'issued — delivery failed'])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now()->startOfDay())
            ->get();

        $count = $expiredCards->count();
        $this->info("Found {$count} potentially expired gift card(s).");

        foreach ($expiredCards as $giftCard) {
            try {
                DB::transaction(function () use ($giftCard) {
                    $oldBalance = (float) $giftCard->current_balance;

                    $giftCard->update([
                        'status' => 'expired',
                        'current_balance' => 0.00,
                    ]);

                    // Ledger transaction for Expiration
                    $giftCard->transactions()->create([
                        'transaction_type' => 'Expiration',
                        'amount' => -$oldBalance,
                        'notes' => "Gift card expired. Locked remaining balance of {$oldBalance}.",
                    ]);

                    // Activity log
                    $giftCard->activityLogs()->create([
                        'action' => 'Expired',
                        'old_value' => (string) $oldBalance,
                        'new_value' => '0.00',
                    ]);

                    $email = $giftCard->recipient_email ?: $giftCard->owner_email;
                    if ($email) {
                        GiftCardMailer::sendExpired($email, [
                            'code' => $giftCard->code,
                            'balance' => $oldBalance,
                            'recipient_name' => $giftCard->recipient_name,
                        ]);
                    }
                });

                $this->info("Gift card ID {$giftCard->id} ({$giftCard->code}) marked as expired.");
            } catch (\Exception $e) {
                $this->error("Failed to expire gift card ID {$giftCard->id}: " . $e->getMessage());
                Log::error("Failed to expire gift card ID {$giftCard->id}: " . $e->getMessage());
            }
        }

        $this->info('Expired gift cards check completed.');
    }
}
