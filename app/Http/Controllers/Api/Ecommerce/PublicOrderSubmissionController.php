<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceCoupon;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\EcommerceQuotation;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PublicOrderSubmissionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'customization' => ['nullable', 'array'],
            'customization.embroidery_type' => ['nullable', 'string', 'max:255'],
            'customization.placement' => ['nullable', 'string', 'max:255'],
            'customization.size_label' => ['nullable', 'string', 'max:255'],
            'customization.quantity_label' => ['nullable', 'string', 'max:255'],
            'customization.thread_colors' => ['nullable', 'array'],
            'customization.thread_colors.*' => ['nullable', 'string', 'max:50'],
            'customization.logo_url' => ['nullable', 'string', 'max:1000'],
            'customization.additional_details' => ['nullable', 'string'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'shipping_method' => ['nullable', 'string', 'max:255'],
            'shipping_amount' => ['nullable', 'numeric', 'min:0'],
            'page_context' => ['nullable', 'array'],
        ]);

        $product = Product::where('is_active', true)->findOrFail($validated['product_id']);
        $pricing = app(ProductCustomizationController::class)->calculatePrice($product, [
            'quantity' => $validated['quantity'],
            'selected_options' => $validated['customization'] ?? [],
            'coupon_code' => $validated['coupon_code'] ?? null,
        ]);
        $unitPrice = $pricing['unit_price'];
        $quantity = $pricing['quantity'];
        $total = $pricing['total_price'];
        $shippingAmount = (float) ($validated['shipping_amount'] ?? 0);
        $appliedCoupon = $pricing['coupon_code']
            ? EcommerceCoupon::where('code', $pricing['coupon_code'])->first()
            : null;
        $couponContext = [
            'product_ids' => [$product->id],
            'user_id' => optional($request->user())->id,
            'customer_email' => $validated['customer_email'],
        ];

        if ($pricing['coupon_code'] && (! $appliedCoupon || ! $appliedCoupon->isUsableFor((float) ($pricing['subtotal'] ?? $total + $pricing['discount_amount']), $couponContext))) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon is not valid for this order.',
            ], 422);
        }

        if ($appliedCoupon?->discount_type === 'free_shipping') {
            $shippingAmount = max(0, $shippingAmount - $appliedCoupon->shippingDiscountFor($shippingAmount, (float) ($pricing['subtotal'] ?? $total + $pricing['discount_amount']), $couponContext));
        }

        if (data_get($validated, 'page_context.intent') === 'quote') {
            $quote = EcommerceQuotation::create([
                'quote_number' => 'QUO-' . now()->format('Y') . '-' . strtoupper(Str::random(6)),
                'user_id' => optional($request->user())->id,
                'product_id' => $product->id,
                'company_name' => $validated['company_name'] ?? null,
                'customer_name' => $validated['customer_name'],
                'contact_email' => $validated['customer_email'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'quantity' => $quantity,
                'customization' => $validated['customization'] ?? [],
                'metadata' => [
                    'notes' => $validated['notes'] ?? null,
                    'page_context' => $validated['page_context'] ?? [],
                    'pricing' => $pricing,
                    'product_snapshot' => [
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'category' => $product->category?->name,
                        'images' => $product->images ?? [],
                        'attributes' => $product->attributes ?? [],
                    ],
                ],
            'status' => 'pending',
            'total_estimated' => $total,
            'valid_until' => now()->addDays(14)->toDateString(),
        ]);

            return response()->json([
                'success' => true,
                'message' => 'Quote request submitted successfully.',
                'data' => [
                    'quotation' => $quote,
                    'order' => [
                        'id' => null,
                        'order_number' => $quote->quote_number,
                    ],
                ],
            ], 201);
        }

        $order = EcommerceOrder::create([
            'order_number' => EcommerceOrder::generateOrderNumber(),
            'user_id' => optional($request->user())->id,
            'customer_name' => $validated['customer_name'],
            'company_name' => $validated['company_name'] ?? null,
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'notes' => $validated['notes'] ?? null,
            'discount_amount' => $pricing['discount_amount'],
            'subtotal' => $pricing['subtotal'] ?? $total,
            'shipping_method' => $validated['shipping_method'] ?? null,
            'shipping_amount' => $shippingAmount,
            'metadata' => [
                'customization' => $validated['customization'] ?? [],
                'page_context' => $validated['page_context'] ?? [],
                'pricing' => $pricing,
                'product_snapshot' => [
                    'category' => $product->category?->name,
                    'images' => $product->images ?? [],
                    'attributes' => $product->attributes ?? [],
                ],
            ],
            'total_amount' => $total + $shippingAmount,
            'order_date' => Carbon::today(),
        ]);
        $order->statusEvents()->create([
            'user_id' => optional($request->user())->id,
            'status' => 'pending',
            'label' => 'Order submitted',
        ]);

        EcommerceOrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $total,
            'product_options' => $validated['customization'] ?? [],
        ]);

        if ($appliedCoupon) {
            $appliedCoupon->increment('used_count');
        }

        return response()->json([
            'success' => true,
            'message' => 'Order submitted successfully.',
            'data' => [
                'order' => $order->load('items'),
            ],
        ], 201);
    }
}
