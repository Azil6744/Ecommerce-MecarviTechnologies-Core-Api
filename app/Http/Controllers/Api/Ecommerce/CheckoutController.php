<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\EcommerceCart;
use App\Models\EcommerceAddress;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\EcommerceGiftCard;
use App\Models\EcommerceCoupon;
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
            'coupon_code' => 'nullable|string|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'tip_amount' => 'nullable|numeric|min:0',
            'donation_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'packaging_option' => 'nullable|string|max:255',
            'gift_message' => 'nullable|string',
            'gift_card_codes' => 'nullable|array',
            'gift_card_codes.*' => 'string',
            'pay_with_points_item_ids' => 'nullable|array',
            'pay_with_points_item_ids.*' => 'integer',
            'points_redeemed' => 'nullable|integer|min:0',
            'selected_charity' => 'nullable|string|max:255',
            'packaging_amount' => 'nullable|numeric|min:0',
            'item_packaging_configs' => 'nullable|array',
            'add_thank_you_card' => 'nullable|boolean',
            'add_extra_protection' => 'nullable|boolean',
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

            // 1. Process Loyalty Points redemption if selected
            $pointsRedeemed = 0;
            if ($user && !empty($validated['pay_with_points_item_ids'])) {
                $pointsItemIds = $validated['pay_with_points_item_ids'];
                $pointsProducts = \App\Models\Product::whereIn('id', $pointsItemIds)->get()->keyBy('id');

                if ($cartItems->isNotEmpty()) {
                    foreach ($cartItems as $cartItem) {
                        if (in_array($cartItem->product_id, $pointsItemIds)) {
                            $prod = $pointsProducts->get($cartItem->product_id);
                            if ($prod && $prod->loyalty_points_price) {
                                $pointsRedeemed += $prod->loyalty_points_price * $cartItem->quantity;
                                $cartItem->total_price = 0.00;
                                $cartItem->unit_price = 0.00;
                            }
                        }
                    }
                } else {
                    $updatedRequestItems = [];
                    foreach ($requestItems as $item) {
                        $productId = $item['product_id'] ?? null;
                        if ($productId && in_array($productId, $pointsItemIds)) {
                            $prod = $pointsProducts->get($productId);
                            if ($prod && $prod->loyalty_points_price) {
                                $qty = (int)($item['quantity'] ?? 1);
                                $pointsRedeemed += $prod->loyalty_points_price * $qty;
                                $item['total_price'] = 0.00;
                                $item['unit_price'] = 0.00;
                                $item['price'] = 0.00;
                            }
                        }
                        $updatedRequestItems[] = $item;
                    }
                    $requestItems = collect($updatedRequestItems);
                }

                if ($pointsRedeemed > 0) {
                    if ($user->loyalty_points < $pointsRedeemed) {
                        return response()->json([
                            'success' => false,
                            'message' => "Insufficient loyalty points. You need {$pointsRedeemed} points but only have {$user->loyalty_points}."
                        ], 400);
                    }
                    $user->loyalty_points -= $pointsRedeemed;
                    $user->save();
                }
            }

            // General checkout subtotal loyalty points discount redemption
            $generalPointsRedeemed = 0;
            if ($user && !empty($validated['points_redeemed'])) {
                $generalPointsRedeemed = (int)$validated['points_redeemed'];
                if ($generalPointsRedeemed > 0) {
                    if ($user->loyalty_points < $generalPointsRedeemed) {
                        return response()->json([
                            'success' => false,
                            'message' => "Insufficient loyalty points. You need {$generalPointsRedeemed} points but only have {$user->loyalty_points}."
                        ], 400);
                    }
                    $user->loyalty_points -= $generalPointsRedeemed;
                    $user->save();
                    $pointsRedeemed += $generalPointsRedeemed;
                }
            }

            $shippingAmount = round((float) ($validated['shipping_amount'] ?? 0), 2);
            $discountAmount = round((float) ($validated['discount_amount'] ?? 0), 2);

            $itemsSubtotal = $cartItems->isNotEmpty()
                ? (float) $cartItems->sum('total_price')
                : (float) $requestItems->sum(fn ($item) => (float) ($item['total_price'] ?? (($item['unit_price'] ?? $item['price'] ?? 0) * ($item['quantity'] ?? 1))));

            $productIds = $cartItems->isNotEmpty()
                ? $cartItems->pluck('product_id')->filter()->map(fn ($id) => (int) $id)->values()->all()
                : $requestItems->pluck('product_id')->filter()->map(fn ($id) => (int) $id)->values()->all();
            $couponContext = [
                'product_ids' => $productIds,
                'user_id' => $user?->id,
                'customer_email' => $user?->email ?? $request->input('customer_email'),
            ];

            $appliedCoupon = null;
            $couponCode = strtoupper(trim((string) ($validated['coupon_code'] ?? '')));
            if ($couponCode !== '') {
                $coupon = EcommerceCoupon::query()
                    ->with('products:id')
                    ->whereRaw('UPPER(code) = ?', [$couponCode])
                    ->first();

                if (! $coupon || ! $coupon->isUsableFor($itemsSubtotal, $couponContext)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Coupon is not valid for this order.',
                    ], 422);
                }

                $appliedCoupon = $coupon;
                $discountAmount = max($discountAmount, $coupon->discountFor($itemsSubtotal, $couponContext));
            } else {
                $appliedCoupon = $this->bestAutoCoupon($itemsSubtotal, $couponContext);
                if ($appliedCoupon) {
                    $couponCode = $appliedCoupon->code;
                    $discountAmount = max($discountAmount, $appliedCoupon->discountFor($itemsSubtotal, $couponContext));
                }
            }

            if ($appliedCoupon?->discount_type === 'free_shipping') {
                $shippingAmount = max(0, round($shippingAmount - $appliedCoupon->shippingDiscountFor($shippingAmount, $itemsSubtotal, $couponContext), 2));
            }

            // 2. Fetch active settings for Taxes, Loyalty Point Earn, and Charity
            $settings = \App\Models\SiteSetting::first();
            $taxRate = $settings && $settings->tax_enabled ? (float)$settings->tax_rate : 0.00;
            $taxAmount = $settings && $settings->tax_enabled ? round($itemsSubtotal * ($taxRate / 100), 2) : round((float) ($validated['tax_amount'] ?? 0), 2);

            $tipAmount = round((float) ($validated['tip_amount'] ?? 0), 2);
            $donationAmount = round((float) ($validated['donation_amount'] ?? 0), 2);
            $packagingAmount = round((float) ($validated['packaging_amount'] ?? 0), 2);

            // Subtotal + shipping + tax + tip + donation + packaging - discount
            $originalTotalAmount = round((float) ($itemsSubtotal + $shippingAmount + $taxAmount + $tipAmount + $donationAmount + $packagingAmount - $discountAmount), 2);
            $totalAmount = max(0.00, $originalTotalAmount);

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

            // 3. Award Loyalty Points on order paid amount
            $pointsEarned = 0;
            if ($user && $totalAmount > 0) {
                $earnPerUnitPrice = $settings && $settings->loyalty_points_earned_per_unit_price > 0 ? (float)$settings->loyalty_points_earned_per_unit_price : 50.00;
                $earnPoints = $settings ? (int)$settings->loyalty_points_earned_points : 2;
                $pointsEarned = (int)(floor($totalAmount / $earnPerUnitPrice) * $earnPoints);
                
                // Points start as pending, only awarded to user balance when completed/delivered.
            }

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
                'tip_amount' => $tipAmount,
                'donation_amount' => $donationAmount,
                'loyalty_points_earned' => $pointsEarned,
                'loyalty_points_redeemed' => $pointsRedeemed,
                'shipping_address' => EcommerceOrderController::formatAddress($shippingAddress),
                'billing_address' => EcommerceOrderController::formatAddress($billingAddress),
                'payment_method' => $validated['payment_method'],
                'shipping_method' => $validated['shipping_method'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'metadata' => [
                    'packaging_option' => $validated['packaging_option'] ?? null,
                    'packaging_amount' => $packagingAmount ?? 0,
                    'item_packaging_configs' => $validated['item_packaging_configs'] ?? null,
                    'add_thank_you_card' => $validated['add_thank_you_card'] ?? false,
                    'add_extra_protection' => $validated['add_extra_protection'] ?? false,
                    'gift_message' => $validated['gift_message'] ?? null,
                    'coupon_code' => $appliedCoupon?->code,
                    'coupon' => $appliedCoupon?->toManagementArray(),
                    'checkout_payload' => $validated,
                ],
                'order_number' => EcommerceOrder::generateOrderNumber(),
                'order_date' => Carbon::today(),
            ];

            $order = EcommerceOrder::create($orderData);

            if ($appliedCoupon) {
                $appliedCoupon->increment('used_count');
            }

            // Log Donation transaction in the database if donation was made
            if ($donationAmount > 0) {
                $charityName = $validated['selected_charity'] ?? 'Feeding America';
                $charity = \App\Models\Charity::where('name', $charityName)->first();
                $charityCategory = $charity ? $charity->category : 'Charity';
                $charityLogo = $charity ? $charity->logo_svg_type : 'generic_charity';

                $donorName = $user ? $user->name : ($order->customer_name ?? 'Guest Customer');
                $donorEmail = $user ? $user->email : ($order->customer_email ?? 'guest@example.com');

                $pmBrand = 'Visa';
                $pmDetails = '**** 4242';
                $methodLower = strtolower((string)($validated['payment_method'] ?? ''));
                if ($methodLower === 'paypal') {
                    $pmBrand = 'PayPal';
                    $pmDetails = 'PayPal';
                } else if ($methodLower === 'apple_pay') {
                    $pmBrand = 'Apple Pay';
                    $pmDetails = 'Apple Pay';
                } else if ($methodLower === 'google_pay') {
                    $pmBrand = 'Google Pay';
                    $pmDetails = 'Google Pay';
                } else if ($methodLower === 'saved_card') {
                    $pmBrand = 'Visa';
                    $pmDetails = 'Saved Card';
                }

                \App\Models\Donation::create([
                    'order_id' => $order->order_number,
                    'txn_id' => 'TXN-' . rand(10000000, 99999999),
                    'donor_name' => $donorName,
                    'donor_email' => $donorEmail,
                    'charity_name' => $charityName,
                    'charity_category' => $charityCategory,
                    'charity_logo_type' => $charityLogo,
                    'amount' => $donationAmount,
                    'payment_method_brand' => $pmBrand,
                    'payment_method_details' => $pmDetails,
                    'payment_method_email' => $user ? $user->email : null,
                    'status' => 'Completed',
                ]);
            }

            // Log Loyalty points transactions in the database
            if ($user) {
                $ratio = 0.01;
                if ($settings && $settings->loyalty_settings) {
                    $loyalty = json_decode($settings->loyalty_settings, true);
                    $ratio = (float)($loyalty['points_to_dollar_ratio'] ?? 0.01);
                }

                // Points earned (pending status)
                if ($pointsEarned > 0) {
                    \App\Models\EcommerceLoyaltyTransaction::create([
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'transaction_type' => 'earned',
                        'points' => $pointsEarned,
                        'dollar_value' => $pointsEarned * $ratio,
                        'status' => 'pending',
                        'reason' => "Points pending for order {$order->order_number}",
                    ]);
                }

                // Points redeemed (redeemed status)
                if ($pointsRedeemed > 0) {
                    \App\Models\EcommerceLoyaltyTransaction::create([
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'transaction_type' => 'redeemed',
                        'points' => -$pointsRedeemed,
                        'dollar_value' => $pointsRedeemed * $ratio,
                        'status' => 'redeemed',
                        'reason' => "Redeemed points on order {$order->order_number}",
                    ]);
                }
            }

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

    private function bestAutoCoupon(float $itemsSubtotal, array $context): ?EcommerceCoupon
    {
        return EcommerceCoupon::query()
            ->with('products:id')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->get()
            ->filter(fn (EcommerceCoupon $coupon) => ($coupon->metadata['apply_method'] ?? 'code') === 'auto')
            ->filter(fn (EcommerceCoupon $coupon) => $coupon->isUsableFor($itemsSubtotal, $context))
            ->sortByDesc(fn (EcommerceCoupon $coupon) => $coupon->discount_type === 'free_shipping'
                ? 0
                : $coupon->discountFor($itemsSubtotal, $context))
            ->first();
    }
}
