<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceGiftCardOrder;
use App\Models\EcommerceGiftCard;
use App\Models\User;
use App\Support\GiftCardMailer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GiftCardOrderController extends Controller
{
    /**
     * Place a gift card order.
     */
    public function store(Request $request)
    {
        $request->validate([
            'buyer_name' => 'nullable|string|max:255',
            'buyer_email' => 'nullable|email|max:255',
            'recipient_name' => 'required|string|max:255',
            'recipient_email' => 'required|email|max:255',
            'personal_message' => 'nullable|string',
            'giftcard_amount' => 'required|numeric|min:0.01',
            'delivery_date' => 'nullable|date',
        ]);

        $user = $request->user();
        $buyerName = $request->buyer_name ?: ($user?->name ?: 'Guest');
        $buyerEmail = $request->buyer_email ?: ($user?->email ?: 'guest@example.com');

        do {
            $orderNumber = 'GCO-' . mt_rand(100000, 999999) . '-' . time();
        } while (EcommerceGiftCardOrder::where('order_number', $orderNumber)->exists());

        $order = EcommerceGiftCardOrder::create([
            'order_number' => $orderNumber,
            'customer_id' => $user?->id,
            'buyer_name' => $buyerName,
            'buyer_email' => $buyerEmail,
            'recipient_name' => $request->recipient_name,
            'recipient_email' => $request->recipient_email,
            'personal_message' => $request->personal_message,
            'giftcard_amount' => $request->giftcard_amount,
            'payment_status' => 'pending',
            'order_status' => 'Payment Pending',
            'delivery_date' => $request->delivery_date,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gift card order placed successfully.',
            'data' => $order
        ], 201);
    }

    /**
     * Pay for the gift card order (mock payment).
     */
    public function pay(Request $request)
    {
        $request->validate([
            'gift_card_order_id' => 'required|exists:ecommerce_gift_card_orders,id',
            'payment_method' => 'required|string',
            'payment_token' => 'required|string',
        ]);

        $order = EcommerceGiftCardOrder::findOrFail($request->gift_card_order_id);

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Order is already paid.'
            ], 400);
        }

        $token = $request->payment_token;
        if (str_starts_with($token, 'test_card_') || str_starts_with($token, 'tok_')) {
            $order->update([
                'payment_status' => 'paid',
                'order_status' => 'Pending Gift Card Issue',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully.',
                'data' => $order
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid payment token.'
        ], 400);
    }

    /**
     * List all gift card orders (Admin).
     */
    public function index(Request $request)
    {
        $query = EcommerceGiftCardOrder::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('buyer_name', 'like', "%{$search}%")
                  ->orWhere('buyer_email', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('recipient_email', 'like', "%{$search}%")
                  ->orWhere('order_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        $orders = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Issue the gift card for a paid order (Admin).
     */
    public function issue(Request $request, $id)
    {
        $order = EcommerceGiftCardOrder::findOrFail($id);

        if ($order->payment_status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Order must be paid before issuing the gift card.'
            ], 400);
        }

        if ($order->order_status === 'Gift Card Issued' || $order->order_status === 'Gift Card Delivered') {
            return response()->json([
                'success' => false,
                'message' => 'Gift card has already been issued for this order.'
            ], 400);
        }

        return DB::transaction(function () use ($order, $request) {
            do {
                $code = '';
                for ($i = 0; $i < 15; $i++) {
                    $code .= random_int(0, 9);
                }
            } while (EcommerceGiftCard::where('code', $code)->exists());

            $recipientUser = User::where('email', $order->recipient_email)->first();
            $expiresAt = now()->addYear();

            $giftCard = EcommerceGiftCard::create([
                'user_id' => $recipientUser?->id,
                'order_id' => $order->id,
                'code' => $code,
                'recipient_name' => $order->recipient_name,
                'recipient_email' => $order->recipient_email,
                'sender_name' => $order->buyer_name,
                'initial_balance' => $order->giftcard_amount,
                'current_balance' => $order->giftcard_amount,
                'status' => 'active',
                'expires_at' => $expiresAt,
                'delivery_type' => 'Email',
                'message' => $order->personal_message,
                'purchased_at' => now(),
                'buyer_user_id' => $order->customer_id,
                'buyer_name' => $order->buyer_name,
                'buyer_email' => $order->buyer_email,
                'owner_email' => $order->recipient_email,
                'issue_type' => 'Purchased',
                'issued_by_admin_id' => $request->user()?->id,
            ]);

            $giftCard->transactions()->create([
                'transaction_type' => 'Issue',
                'amount' => $order->giftcard_amount,
                'notes' => 'Gift card issued for order ' . $order->order_number,
                'created_by' => $request->user()?->id,
            ]);

            $giftCard->activityLogs()->create([
                'action' => 'Issued',
                'admin_id' => $request->user()?->id,
                'user_id' => $order->customer_id,
                'old_value' => null,
                'new_value' => json_encode($giftCard->only(['id', 'code', 'initial_balance', 'recipient_email'])),
                'ip_address' => $request->ip(),
            ]);

            $order->update([
                'order_status' => 'Gift Card Issued'
            ]);

            $emailSent = GiftCardMailer::sendIssued($order->recipient_email, [
                'code' => $code,
                'balance' => $order->giftcard_amount,
                'message' => $order->personal_message,
                'recipient_name' => $order->recipient_name,
                'sender_name' => $order->buyer_name,
                'expires_at' => $expiresAt->toDateString(),
            ]);

            if ($emailSent) {
                $order->update(['order_status' => 'Gift Card Delivered']);
                $giftCard->update(['status' => 'delivered']);
            } else {
                $order->update(['order_status' => 'Delivery Failed']);
                $giftCard->update(['status' => 'Issued — Delivery Failed']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Gift card issued successfully.',
                'data' => [
                    'order' => $order->fresh(),
                    'gift_card' => $giftCard->fresh()
                ]
            ]);
        });
    }
}
