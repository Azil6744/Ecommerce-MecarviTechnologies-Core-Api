<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\EcommerceCart;
use App\Models\EcommerceAddress;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\EcommerceGiftCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CheckoutController extends Controller
{
    /**
     * Process the checkout request and generate an order.
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'items' => 'nullable|array',
            'shipping_address_id' => 'nullable',
            'billing_address_id' => 'nullable',
            'shipping_method' => 'nullable|string|max:255',
            'payment_method' => 'required|string|max:255',
            'shipping_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'packaging_option' => 'nullable|string|max:255',
            'gift_message' => 'nullable|string',
            'gift_card_codes' => 'nullable|array',
            'gift_card_codes.*' => 'string',
        ]);

        try {
            DB::beginTransaction();

            $user = $request->user();
            $cart = EcommerceCart::with('items.product')
                ->where('user_id', $user?->id)
                ->where('status', 'active')
                ->first();

            if (! $user) {
                $emailFromRequest = strtolower(trim((string) ($request->input('customer_email') ?? '')));
                if ($emailFromRequest !== '') {
                    $user = \App\Models\User::whereRaw('LOWER(email) = ?', [$emailFromRequest])->first();
                }
            }

            $cartItems = $cart?->items ?? collect();
            $requestItems = collect($validated['items'] ?? []);

            if ($cartItems->isEmpty() && $requestItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty.',
                ], 422);
            }

            $shippingAmount = round((float) ($validated['shipping_amount'] ?? 0), 2);
            $discountAmount = round((float) ($validated['discount_amount'] ?? 0), 2);
            $taxAmount = round((float) ($validated['tax_amount'] ?? 0), 2);
            $itemsSubtotal = $cartItems->isNotEmpty()
                ? (float) $cartItems->sum('total_price')
                : (float) $requestItems->sum(fn ($item) => (float) ($item['total_price'] ?? (($item['unit_price'] ?? $item['price'] ?? 0) * ($item['quantity'] ?? 1))));
            
            $originalTotalAmount = round((float) ($validated['total_amount'] ?? ($itemsSubtotal + $shippingAmount + $taxAmount - $discountAmount)), 2);
            $totalAmount = $originalTotalAmount;

            // Gift card validation
            $giftCardCodes = $validated['gift_card_codes'] ?? [];
            $giftCardsToRedeem = [];
            $totalGiftCardApplied = 0.00;

            if (!empty($giftCardCodes)) {
                foreach ($giftCardCodes as $code) {
                    $giftCard = EcommerceGiftCard::where('code', $code)->first();

                    if (!$giftCard) {
                        return response()->json([
                            'success' => false,
                            'message' => "Invalid gift card code: {$code}"
                        ], 400);
                    }

                    if (in_array(strtolower($giftCard->status), ['disabled', 'expired', 'fully used', 'redeemed', 'cancelled'])) {
                        return response()->json([
                            'success' => false,
                            'message' => "Gift card {$code} is not active."
                        ], 400);
                    }

                    if ($giftCard->expires_at && $giftCard->expires_at->isPast()) {
                        return response()->json([
                            'success' => false,
                            'message' => "Gift card {$code} has expired."
                        ], 400);
                    }

                    if ($giftCard->current_balance <= 0) {
                        return response()->json([
                            'success' => false,
                            'message' => "Gift card {$code} has no remaining balance."
                        ], 400);
                    }

                    $giftCardsToRedeem[] = $giftCard;
                }

                // Calculate total gift card amount to apply
                $tempTotal = $totalAmount;
                foreach ($giftCardsToRedeem as $giftCard) {
                    $cardBalance = (float) $giftCard->current_balance;
                    $deduct = min($tempTotal, $cardBalance);
                    $tempTotal -= $deduct;
                    $totalGiftCardApplied += $deduct;

                    if ($tempTotal <= 0) {
                        break;
                    }
                }

                // Deduct from total amount
                $totalAmount = max(0.00, round($totalAmount - $totalGiftCardApplied, 2));
            }

            $shippingAddress = $this->addressForUser($user?->id, $validated['shipping_address_id'] ?? null);
            $billingAddress = $this->addressForUser($user?->id, $validated['billing_address_id'] ?? $validated['shipping_address_id'] ?? null);

            $orderData = [
                'user_id' => $user?->id,
                'customer_name' => $user?->name ?? 'Guest Customer',
                'customer_email' => $user?->email ?? 'guest@example.com',
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'total_amount' => $totalAmount,
                'subtotal' => round($itemsSubtotal, 2),
                'shipping_amount' => $shippingAmount,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'shipping_address' => EcommerceOrderController::formatAddress($shippingAddress),
                'billing_address' => EcommerceOrderController::formatAddress($billingAddress),
                'payment_method' => $validated['payment_method'],
                'shipping_method' => $validated['shipping_method'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'metadata' => [
                    'packaging_option' => $validated['packaging_option'] ?? null,
                    'gift_message' => $validated['gift_message'] ?? null,
                    'checkout_payload' => $validated,
                ],
                'order_number' => EcommerceOrder::generateOrderNumber(),
                'order_date' => Carbon::today(),
            ];

            $order = EcommerceOrder::create($orderData);
            $order->statusEvents()->create([
                'user_id' => $user?->id,
                'status' => 'pending',
                'label' => 'Order placed',
            ]);

            // Deduct gift card balances and create ledger transactions
            if (!empty($giftCardsToRedeem)) {
                $remainingToDeduct = $originalTotalAmount;
                $actualApplied = 0.00;

                foreach ($giftCardsToRedeem as $giftCard) {
                    if ($remainingToDeduct <= 0) {
                        break;
                    }

                    $oldBalance = (float) $giftCard->current_balance;
                    $deducted = min($remainingToDeduct, $oldBalance);
                    $newBalance = $oldBalance - $deducted;
                    $remainingToDeduct -= $deducted;
                    $actualApplied += $deducted;

                    $newStatus = $newBalance <= 0 ? 'fully used' : 'partially used';
                    $giftCard->update([
                        'current_balance' => $newBalance,
                        'status' => $newStatus,
                        'last_used_at' => now(),
                    ]);

                    // Ledger transaction for Redemption
                    $giftCard->transactions()->create([
                        'transaction_type' => 'Redemption',
                        'amount' => -$deducted,
                        'order_id' => $order->id,
                        'notes' => "Redeemed {$deducted} for order {$order->order_number}.",
                        'created_by' => $user?->id,
                    ]);

                    // Activity log
                    $giftCard->activityLogs()->create([
                        'action' => 'Redeemed',
                        'user_id' => $user?->id,
                        'old_value' => (string) $oldBalance,
                        'new_value' => (string) $newBalance,
                    ]);
                }

                // If fully paid by gift cards, mark order as paid
                if ($remainingToDeduct <= 0) {
                    $order->update([
                        'payment_status' => 'paid',
                    ]);
                }

                // Store gift card info in metadata
                $meta = $order->metadata ?? [];
                $meta['gift_cards_applied'] = collect($giftCardsToRedeem)->map(fn($gc) => $gc->code)->toArray();
                $meta['gift_card_deduction'] = $actualApplied;
                $order->update(['metadata' => $meta]);
            }

            if ($cartItems->isNotEmpty()) {
                foreach ($cartItems as $cartItem) {
                    EcommerceOrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'product_name' => $cartItem->product?->name ?? 'Product',
                        'product_sku' => $cartItem->product?->sku,
                        'quantity' => $cartItem->quantity,
                        'unit_price' => $cartItem->unit_price,
                        'total_price' => $cartItem->total_price,
                        'product_options' => $cartItem->options ?? [],
                    ]);
                }

                $cart->items()->delete();
                $cart->forceFill(['status' => 'converted', 'total_amount' => 0])->save();
            } else {
                foreach ($requestItems as $item) {
                    EcommerceOrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'] ?? null,
                        'product_name' => $item['product_name'] ?? $item['name'] ?? 'Product',
                        'product_sku' => $item['product_sku'] ?? $item['sku'] ?? null,
                        'quantity' => (int) ($item['quantity'] ?? 1),
                        'unit_price' => (float) ($item['unit_price'] ?? $item['price'] ?? 0),
                        'total_price' => (float) ($item['total_price'] ?? (($item['unit_price'] ?? $item['price'] ?? 0) * ($item['quantity'] ?? 1))),
                        'product_options' => $item['product_options'] ?? $item['options'] ?? [],
                    ]);
                }
            }

            DB::commit();

            if ($order->user_id && $order->customer_email) {
                $customerEmail = strtolower(trim((string) $order->customer_email));
                if ($customerEmail !== '') {
                    EcommerceOrder::whereNull('user_id')
                        ->whereRaw('LOWER(customer_email) = ?', [$customerEmail])
                        ->update(['user_id' => $order->user_id]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully.',
                'order' => $order->load('items.product'),
                'data' => $order->load('items.product'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process checkout: ' . $e->getMessage()
            ], 500);
        }
    }

    private function addressForUser(?int $userId, mixed $addressId): ?EcommerceAddress
    {
        if (! $userId || ! is_numeric($addressId)) {
            return null;
        }

        return EcommerceAddress::where('user_id', $userId)->whereKey((int) $addressId)->first();
    }
}
