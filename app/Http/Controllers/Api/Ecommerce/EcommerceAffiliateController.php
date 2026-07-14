<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceAffiliate;
use Illuminate\Support\Facades\Schema;

class EcommerceAffiliateController extends Controller
{
    public function getSettings(Request $request)
    {
        $settings = \App\Models\SiteSetting::first();
        return response()->json([
            'success' => true,
            'data' => [
                'referral_reward_referrer' => $settings ? (float)$settings->referral_reward_referrer : 0.00,
                'referral_reward_referee' => $settings ? (float)$settings->referral_reward_referee : 0.00,
                'referral_commission_percentage' => $settings ? (float)$settings->referral_commission_percentage : 0.00,
            ]
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'referral_reward_referrer' => 'required|numeric|min:0',
            'referral_reward_referee' => 'required|numeric|min:0',
            'referral_commission_percentage' => 'required|numeric|between:0,100',
        ]);

        $settings = \App\Models\SiteSetting::first();
        if (!$settings) {
            $settings = new \App\Models\SiteSetting();
        }

        $settings->referral_reward_referrer = $validated['referral_reward_referrer'];
        $settings->referral_reward_referee = $validated['referral_reward_referee'];
        $settings->referral_commission_percentage = $validated['referral_commission_percentage'];
        $settings->save();

        return response()->json([
            'success' => true,
            'message' => 'Affiliate settings updated successfully.',
            'data' => [
                'referral_reward_referrer' => (float)$settings->referral_reward_referrer,
                'referral_reward_referee' => (float)$settings->referral_reward_referee,
                'referral_commission_percentage' => (float)$settings->referral_commission_percentage,
            ]
        ]);
    }

    public function referralsList(Request $request)
    {
        $referrals = \App\Models\EcommerceReferral::with(['referrer', 'referred'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $referrals
        ]);
    }

    public function referralCommissionsList(Request $request)
    {
        $commissions = \App\Models\EcommerceReferralCommission::with(['referrer', 'referred', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $commissions
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        // Check if admin to return all, or just user
        if ($user && $user->isSuperAdmin()) {
            return response()->json(['success' => true, 'data' => EcommerceAffiliate::with('user')->get()]);
        }
        
        // Get by user_id if column exists, otherwise all
        if(Schema::hasColumn((new EcommerceAffiliate)->getTable(), 'user_id')) {
            $query = EcommerceAffiliate::where('user_id', $user->id)->with('user');
            return response()->json(['success' => true, 'data' => $query->get()]);
        }

        return response()->json(['success' => true, 'data' => EcommerceAffiliate::with('user')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if(Schema::hasColumn((new EcommerceAffiliate)->getTable(), 'user_id')) {
            $data['user_id'] = $request->user()->id;
        }
        $item = EcommerceAffiliate::create($data);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function show(Request $request, $id)
    {
        $item = EcommerceAffiliate::findOrFail($id);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = EcommerceAffiliate::findOrFail($id);
        $item->update($request->all());
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy(Request $request, $id)
    {
        $item = EcommerceAffiliate::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }

    public function payout(Request $request, $id)
    {
        $commission = \App\Models\EcommerceReferralCommission::findOrFail($id);
        
        if ($commission->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'This commission payout has already been processed.'
            ], 400);
        }

        $commission->update([
            'status' => 'completed',
            'payout_at' => now(),
        ]);

        // Add to referrer's wallet
        $referrer = $commission->referrer;
        if ($referrer) {
            $newBalance = round((float)($referrer->wallet_balance ?? 0) + (float)$commission->commission_amount, 2);
            $referrer->update(['wallet_balance' => $newBalance]);

            \App\Models\EcommerceWalletTransaction::create([
                'user_id' => $referrer->id,
                'type' => 'Affiliate Credit',
                'amount' => $commission->commission_amount,
                'balance_after' => $newBalance,
                'description' => 'Referral commission payout for order #' . ($commission->order ? $commission->order->order_number : 'N/A'),
                'status' => 'Completed',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payout processed successfully.',
            'data' => $commission->load(['referrer', 'referred', 'order'])
        ]);
    }

    public function myReferrals(Request $request)
    {
        $user = $request->user();
        $referrals = \App\Models\EcommerceReferral::where('referrer_id', $user->id)
            ->with(['referred'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $referrals
        ]);
    }

    public function applicationsList(Request $request)
    {
        $applications = \App\Models\EcommerceAffiliateApplication::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $applications
        ]);
    }

    public function approveApplication(Request $request, $id)
    {
        $app = \App\Models\EcommerceAffiliateApplication::findOrFail($id);
        if ($app->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Application has already been processed.'], 400);
        }

        $app->update(['status' => 'approved']);

        // Check if affiliate already exists for this user
        $exists = EcommerceAffiliate::where('user_id', $app->user_id)->exists();
        if (!$exists) {
            $user = \App\Models\User::findOrFail($app->user_id);
            // Generate a unique affiliate code
            $code = strtoupper(substr($user->name ?? 'PARTNER', 0, 3) . rand(1000, 9999));
            while (EcommerceAffiliate::where('affiliate_code', $code)->exists()) {
                $code = strtoupper(substr($user->name ?? 'PARTNER', 0, 3) . rand(1000, 9999));
            }

            EcommerceAffiliate::create([
                'user_id' => $app->user_id,
                'affiliate_code' => $code,
                'total_earnings' => 0.00,
                'total_referrals' => 0,
                'status' => 'Active',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Affiliate application approved successfully.',
        ]);
    }

    public function rejectApplication(Request $request, $id)
    {
        $app = \App\Models\EcommerceAffiliateApplication::findOrFail($id);
        if ($app->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Application has already been processed.'], 400);
        }

        $app->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Affiliate application rejected successfully.',
        ]);
    }

    public function apply(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        // Check if already an affiliate
        if (EcommerceAffiliate::where('user_id', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'You are already an affiliate.'], 400);
        }

        // Check if pending application exists
        $pending = \App\Models\EcommerceAffiliateApplication::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();
        if ($pending) {
            return response()->json(['success' => false, 'message' => 'You already have a pending application.'], 400);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string',
        ]);

        $app = \App\Models\EcommerceAffiliateApplication::create([
            'user_id' => $user->id,
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'data' => $app,
            'message' => 'Affiliate application submitted successfully.',
        ]);
    }
}
