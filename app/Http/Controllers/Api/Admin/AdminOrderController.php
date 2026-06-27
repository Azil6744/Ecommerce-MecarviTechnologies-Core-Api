<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceOrder;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    private const ORDER_STATUSES = [
        'pending',
        'payment_pending',
        'confirmed',
        'processing',
        'proof_ready',
        'proof_revision',
        'approved',
        'in_production',
        'shipped',
        'delivered',
        'completed',
        'cancelled',
        'refunded',
    ];

    /**
     * Display all orders (admin only)
     */
    public function index(Request $request)
    {
        try {
            $query = EcommerceOrder::with(['user', 'items']);

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhere('customer_email', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $orders = $query->orderBy('created_at', 'desc')
                ->paginate(min((int) $request->get('per_page', 20), 100));
            $orders->getCollection()->transform(fn (EcommerceOrder $order) => $this->orderPayload($order));

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get order details
     */
    public function show($id)
    {
        try {
            $order = EcommerceOrder::with(['user', 'items.product', 'proofs', 'verifications', 'statusEvents'])->findOrFail($id);
            return response()->json(['success' => true, 'data' => ['order' => $this->orderPayload($order)]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:' . implode(',', self::ORDER_STATUSES),
                'payment_status' => 'nullable|in:unpaid,paid,failed,refunded,partially_refunded',
                'tracking_carrier' => 'nullable|string|max:255',
                'tracking_number' => 'nullable|string|max:255',
                'tracking_url' => 'nullable|string|max:1000',
                'estimated_delivery_at' => 'nullable|date',
                'note' => 'nullable|string',
            ]);

            $order = EcommerceOrder::findOrFail($id);
            $payload = $request->only([
                'status',
                'payment_status',
                'tracking_carrier',
                'tracking_number',
                'tracking_url',
                'estimated_delivery_at',
            ]);

            if ($request->status === 'shipped' && ! $order->shipped_at) {
                $payload['shipped_at'] = now();
            }

            if (in_array($request->status, ['delivered', 'completed'], true) && ! $order->delivered_at) {
                $payload['delivered_at'] = now();
            }

            $order->update($payload);

            // Handle Loyalty Points transitions based on status changes
            if ($order->user_id) {
                if (in_array($request->status, ['delivered', 'completed'], true)) {
                    // Update pending loyalty points earned on this order to available
                    $pendingTxn = \App\Models\EcommerceLoyaltyTransaction::where('order_id', $order->id)
                        ->where('transaction_type', 'earned')
                        ->where('status', 'pending')
                        ->first();
                    if ($pendingTxn) {
                        $pendingTxn->update(['status' => 'available']);
                        $user = \App\Models\User::find($order->user_id);
                        if ($user) {
                            $user->loyalty_points = max(0, $user->loyalty_points + $pendingTxn->points);
                            $user->save();
                        }
                    }
                } elseif (in_array($request->status, ['cancelled'], true)) {
                    // Reverse earned points
                    $earnedTxns = \App\Models\EcommerceLoyaltyTransaction::where('order_id', $order->id)
                        ->where('transaction_type', 'earned')
                        ->whereIn('status', ['pending', 'available'])
                        ->get();
                    foreach ($earnedTxns as $t) {
                        if ($t->status === 'available') {
                            $user = \App\Models\User::find($order->user_id);
                            if ($user) {
                                $user->loyalty_points = max(0, $user->loyalty_points - $t->points);
                                $user->save();
                            }
                            // Create a reversal entry
                            \App\Models\EcommerceLoyaltyTransaction::create([
                                'user_id' => $order->user_id,
                                'order_id' => $order->id,
                                'transaction_type' => 'reversed',
                                'points' => -$t->points,
                                'dollar_value' => $t->dollar_value,
                                'status' => 'reversed',
                                'reason' => "Reversed points due to order cancellation.",
                            ]);
                        }
                        $t->update(['status' => 'reversed']);
                    }

                    // Return points redeemed back to customer
                    $redeemedTxns = \App\Models\EcommerceLoyaltyTransaction::where('order_id', $order->id)
                        ->where('transaction_type', 'redeemed')
                        ->where('status', 'redeemed')
                        ->get();
                    foreach ($redeemedTxns as $t) {
                        $user = \App\Models\User::find($order->user_id);
                        if ($user) {
                            $user->loyalty_points = max(0, $user->loyalty_points + abs($t->points));
                            $user->save();
                        }
                        // Create a return manual adjustment
                        \App\Models\EcommerceLoyaltyTransaction::create([
                            'user_id' => $order->user_id,
                            'order_id' => $order->id,
                            'transaction_type' => 'manual_added',
                            'points' => abs($t->points),
                            'dollar_value' => $t->dollar_value,
                            'status' => 'available',
                            'reason' => "Returned redeemed points due to order cancellation.",
                        ]);
                        $t->update(['status' => 'reversed']);
                    }
                }
            }

            $order->statusEvents()->create([
                'user_id' => optional($request->user())->id,
                'status' => $request->status,
                'label' => 'Status updated to ' . str_replace('_', ' ', $request->status),
                'note' => $request->note,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated.',
                'data' => ['order' => $this->orderPayload($order->fresh(['user', 'items.product', 'proofs', 'verifications', 'statusEvents']))],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update order.', 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    /**
     * Delete order
     */
    public function destroy($id)
    {
        try {
            EcommerceOrder::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Order deleted.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete order.'], 500);
        }
    }

    /**
     * Get orders summary stats
     */
    public function stats()
    {
        try {
            $total = EcommerceOrder::count();
            $pending = EcommerceOrder::where('status', 'pending')->count();
            $processing = EcommerceOrder::where('status', 'processing')->count();
            $completed = EcommerceOrder::where('status', 'completed')->count();
            $cancelled = EcommerceOrder::where('status', 'cancelled')->count();
            $revenue = EcommerceOrder::where('status', 'completed')->sum('total_amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'pending' => $pending,
                    'processing' => $processing,
                    'completed' => $completed,
                    'cancelled' => $cancelled,
                    'revenue' => round($revenue, 2),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get stats.'], 500);
        }
    }

    private function orderPayload(EcommerceOrder $order): array
    {
        $order->loadMissing(['user', 'items.product', 'proofs', 'verifications', 'statusEvents']);
        $subtotal = (float) ($order->subtotal ?: $order->items->sum('total_price'));

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'user_id' => $order->user_id,
            'user' => $order->user,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'company_name' => $order->company_name,
            'status' => strtolower($order->status),
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'shipping_method' => $order->shipping_method,
            'currency' => $order->currency ?? 'USD',
            'subtotal' => round($subtotal, 2),
            'shipping_amount' => (float) ($order->shipping_amount ?? 0),
            'discount_amount' => (float) ($order->discount_amount ?? 0),
            'tax_amount' => (float) ($order->tax_amount ?? 0),
            'total_amount' => (float) $order->total_amount,
            'shipping_address' => $order->shipping_address,
            'billing_address' => $order->billing_address,
            'tracking_carrier' => $order->tracking_carrier,
            'tracking_number' => $order->tracking_number,
            'tracking_url' => $order->tracking_url,
            'estimated_delivery_at' => optional($order->estimated_delivery_at)->toIso8601String(),
            'shipped_at' => optional($order->shipped_at)->toIso8601String(),
            'delivered_at' => optional($order->delivered_at)->toIso8601String(),
            'notes' => $order->notes,
            'metadata' => $order->metadata,
            'order_date' => optional($order->order_date)->toIso8601String(),
            'created_at' => optional($order->created_at)->toIso8601String(),
            'updated_at' => optional($order->updated_at)->toIso8601String(),
            'items' => $order->items,
            'proofs' => $order->proofs,
            'verifications' => $order->verifications,
            'status_events' => $order->statusEvents,
        ];
    }
}
