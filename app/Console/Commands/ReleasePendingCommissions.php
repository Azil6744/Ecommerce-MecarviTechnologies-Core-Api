<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EcommerceReferralCommission;
use App\Models\User;
use App\Models\EcommerceWalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReleasePendingCommissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'affiliate:release-commissions';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Release pending referral commissions that have passed their 10-day holding period to referrers\' wallets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting release of pending commissions...');

        $commissions = EcommerceReferralCommission::where('status', 'pending')
            ->where('payout_at', '<=', now())
            ->get();

        if ($commissions->isEmpty()) {
            $this->info('No pending commissions found to release.');
            return 0;
        }

        $releasedCount = 0;

        foreach ($commissions as $commission) {
            try {
                DB::transaction(function () use ($commission, &$releasedCount) {
                    $referrer = User::lockForUpdate()->find($commission->referrer_id);

                    if ($referrer && $commission->commission_amount > 0) {
                        \App\Services\WalletService::adjustWallet(
                            $referrer->id,
                            $commission->commission_amount,
                            'Affiliate Earned',
                            'Referral commission released for order #' . ($commission->order ? $commission->order->order_number : $commission->order_id),
                            $commission->order_id
                        );

                        // Increment affiliate earnings
                        $affiliate = $referrer->affiliate;
                        if ($affiliate) {
                            $affiliate->increment('total_earnings', $commission->commission_amount);
                        }
                    }

                    // Update commission record status
                    $commission->update(['status' => 'completed']);
                    $releasedCount++;
                });
            } catch (\Exception $e) {
                $this->error('Failed to release commission ID ' . $commission->id . ': ' . $e->getMessage());
                Log::error('Commission release error for ID ' . $commission->id . ': ' . $e->getMessage());
            }
        }

        $this->info('Successfully released ' . $releasedCount . ' referral commissions.');
        return 0;
    }
}
