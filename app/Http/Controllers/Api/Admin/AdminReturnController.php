<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceReturn;
use Illuminate\Http\Request;

class AdminReturnController extends Controller
{
    /**
     * Get all return requests
     */
    public function index(Request $request)
    {
        $query = EcommerceReturn::with(['order.items.product', 'user'])->latest();

        if ($request->has('status')) {
            $query->whereRaw('LOWER(status) = ?', [strtolower($request->status)]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhere('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $returns = $query->paginate(min((int) $request->get('per_page', 15), 100));

        return response()->json($returns);
    }

    /**
     * Show return details
     */
    public function show(EcommerceReturn $return)
    {
        return response()->json($return->load(['order.items.product', 'user']));
    }

    /**
     * Update return status
     */
    public function updateStatus(Request $request, EcommerceReturn $return)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,completed,refunded',
        ]);

        $payload = ['status' => $request->status];

        if ($request->status === 'approved') {
            $payload['approved_at'] = now();
        }

        if (in_array($request->status, ['completed', 'refunded'], true)) {
            $payload['refunded_at'] = now();
        }

        $return->update($payload);

        return response()->json($return);
    }

    /**
     * Process return approval and refund
     */
    public function approve(Request $request, EcommerceReturn $return)
    {
        $return->loadMissing('order');
        
        $return->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Process refund to user wallet
        if ($return->user_id && $return->refund_amount > 0) {
            \App\Services\WalletService::adjustWallet(
                $return->user_id,
                $return->refund_amount,
                'Refund credit',
                'Refund for returned order #' . $return->order_number,
                $return->order_id
            );
        }

        // Reverse earned loyalty points on refund
        if ($return->user_id && $return->order) {
            $order = $return->order;
            $pointsEarned = (int) ($order->loyalty_points_earned ?? 0);
            if ($pointsEarned > 0) {
                $orderTotal = (float) ($order->subtotal ?: $order->total_amount);
                $refundAmount = (float) $return->refund_amount;
                $isFullRefund = ($refundAmount <= 0) || ($refundAmount >= $orderTotal);

                $pointsToReverse = $isFullRefund
                    ? $pointsEarned
                    : (int) round($pointsEarned * ($refundAmount / ($orderTotal ?: 1.00)));

                $pointsToReverse = min($pointsEarned, $pointsToReverse);

                if ($pointsToReverse > 0) {
                    \App\Services\LoyaltyService::adjustPoints(
                        $return->user_id,
                        $pointsToReverse,
                        'reversed',
                        "Reversed points due to refund for order {$return->order_number}",
                        $return->order_id,
                        'reversed'
                    );
                }
            }
        }

        return response()->json($return);
    }
}
