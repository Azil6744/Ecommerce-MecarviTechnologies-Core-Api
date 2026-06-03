<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceCoupon;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\EcommerceQuotation;
use App\Models\Product;
use App\Models\ProductCustomizationDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductCustomizationController extends Controller
{
    public function pricePreview(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
            'selected_options' => ['nullable', 'array'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->calculatePrice($product, $validated),
        ]);
    }

    public function storeDraft(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
            'selected_options' => ['nullable', 'array'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'metadata' => ['nullable', 'array'],
        ]);

        $pricing = $this->calculatePrice($product, $validated);
        $draft = ProductCustomizationDraft::create([
            'product_id' => $product->id,
            'user_id' => optional($request->user())->id,
            'session_id' => $this->sessionId($request),
            'selected_options' => $validated['selected_options'] ?? [],
            'quantity' => $pricing['quantity'],
            'unit_price' => $pricing['unit_price'],
            'setup_fee' => $pricing['setup_fee'],
            'discount_amount' => $pricing['discount_amount'],
            'total_price' => $pricing['total_price'],
            'coupon_code' => $pricing['coupon_code'],
            'metadata' => $validated['metadata'] ?? [],
        ]);

        return response()->json([
            'success' => true,
            'data' => $draft->load('product', 'files'),
        ], 201);
    }

    public function updateDraft(Request $request, ProductCustomizationDraft $draft)
    {
        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
            'selected_options' => ['nullable', 'array'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'metadata' => ['nullable', 'array'],
        ]);

        $pricing = $this->calculatePrice($draft->product, array_merge($draft->toArray(), $validated));
        $draft->update([
            'selected_options' => $validated['selected_options'] ?? $draft->selected_options ?? [],
            'quantity' => $pricing['quantity'],
            'unit_price' => $pricing['unit_price'],
            'setup_fee' => $pricing['setup_fee'],
            'discount_amount' => $pricing['discount_amount'],
            'total_price' => $pricing['total_price'],
            'coupon_code' => $pricing['coupon_code'],
            'metadata' => array_merge($draft->metadata ?? [], $validated['metadata'] ?? []),
        ]);

        return response()->json([
            'success' => true,
            'data' => $draft->fresh()->load('product', 'files'),
        ]);
    }

    public function uploadFile(Request $request, ProductCustomizationDraft $draft)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,ai,eps,webp', 'max:10240'],
            'file_type' => ['nullable', 'string', 'max:50'],
        ]);

        $file = $request->file('file');
        $path = $file->store('product-customizations/' . $draft->id, 'public');

        $record = $draft->files()->create([
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize() ?: 0,
            'file_type' => $request->input('file_type', 'logo'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $record,
        ], 201);
    }

    public function submitOrder(Request $request, ProductCustomizationDraft $draft)
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($request, $draft, $validated) {
            $product = $draft->product;
            $customization = $this->customizationPayload($draft);

            $order = EcommerceOrder::create([
                'order_number' => EcommerceOrder::generateOrderNumber(),
                'user_id' => optional($request->user())->id ?? $draft->user_id,
                'customer_name' => $validated['customer_name'],
                'company_name' => $validated['company_name'] ?? null,
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $validated['payment_method'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'metadata' => [
                    'customization' => $customization,
                    'draft_id' => $draft->id,
                ],
                'total_amount' => $draft->total_price,
                'discount_amount' => $draft->discount_amount,
                'order_date' => Carbon::today(),
            ]);

            EcommerceOrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $draft->quantity,
                'unit_price' => $draft->unit_price,
                'total_price' => $draft->total_price,
                'product_options' => $customization,
            ]);

            if ($draft->coupon_code) {
                EcommerceCoupon::where('code', $draft->coupon_code)->increment('used_count');
            }

            $draft->update(['status' => 'converted']);

            return response()->json([
                'success' => true,
                'message' => 'Order submitted successfully.',
                'data' => ['order' => $order->load('items')],
            ], 201);
        });
    }

    public function submitQuote(Request $request, ProductCustomizationDraft $draft)
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $quote = EcommerceQuotation::create([
            'quote_number' => 'QUO-' . now()->format('Y') . '-' . strtoupper(Str::random(6)),
            'user_id' => optional($request->user())->id ?? $draft->user_id,
            'product_id' => $draft->product_id,
            'company_name' => $validated['company_name'] ?? null,
            'customer_name' => $validated['customer_name'],
            'contact_email' => $validated['customer_email'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'quantity' => $draft->quantity,
            'customization' => $this->customizationPayload($draft),
            'metadata' => [
                'notes' => $validated['notes'] ?? null,
                'draft_id' => $draft->id,
            ],
            'status' => 'pending',
            'total_estimated' => $draft->total_price,
            'valid_until' => now()->addDays(14)->toDateString(),
        ]);

        $draft->update(['status' => 'submitted']);

        return response()->json([
            'success' => true,
            'message' => 'Quote request submitted successfully.',
            'data' => ['quotation' => $quote],
        ], 201);
    }

    public function coupons(Product $product)
    {
        $coupons = EcommerceCoupon::query()
            ->where('is_active', true)
            ->where(function ($query) use ($product) {
                $query->whereDoesntHave('products')
                    ->orWhereHas('products', fn ($productQuery) => $productQuery->where('products.id', $product->id));
            })
            ->orderBy('id')
            ->get();

        return response()->json(['success' => true, 'data' => $coupons]);
    }

    public function related(Product $product)
    {
        $related = $product->relatedProducts()
            ->where('is_active', true)
            ->orderBy('product_related_products.sort_order')
            ->get();

        if ($related->isEmpty()) {
            $related = Product::query()
                ->where('is_active', true)
                ->where('id', '!=', $product->id)
                ->where('category_id', $product->category_id)
                ->latest()
                ->limit(8)
                ->get();
        }

        return response()->json(['success' => true, 'data' => $related]);
    }

    public function calculatePrice(Product $product, array $payload): array
    {
        $quantity = max(1, (int) ($payload['quantity'] ?? 1));
        $unitPrice = (float) ($product->sale_price ?? $product->price ?? 0);
        $setupFee = (float) data_get($product->attributes, 'setup_fee', 0);
        $selectedOptions = $payload['selected_options'] ?? [];
        $optionAdjustments = $this->optionAdjustments($product, $selectedOptions, $quantity);
        $ruleAdjustments = $this->ruleAdjustments($product, $selectedOptions, $quantity, $unitPrice);
        $lineSubtotal = ($unitPrice * $quantity) + $optionAdjustments['total'] + $ruleAdjustments['total'];
        $subtotal = round(max(0, $lineSubtotal + $setupFee), 2);
        $couponCode = isset($payload['coupon_code']) ? strtoupper(trim((string) $payload['coupon_code'])) : null;
        $discount = 0;

        if ($couponCode) {
            $coupon = EcommerceCoupon::where('code', $couponCode)
                ->where(function ($query) use ($product) {
                    $query->whereDoesntHave('products')
                        ->orWhereHas('products', fn ($productQuery) => $productQuery->where('products.id', $product->id));
                })
                ->first();

            $discount = $coupon ? $coupon->discountFor($subtotal) : 0;
        }

        return [
            'quantity' => $quantity,
            'unit_price' => round($unitPrice, 2),
            'setup_fee' => round($setupFee, 2),
            'option_adjustments' => round($optionAdjustments['total'], 2),
            'pricing_rule_adjustments' => round($ruleAdjustments['total'], 2),
            'subtotal' => $subtotal,
            'discount_amount' => round($discount, 2),
            'total_price' => round(max(0, $subtotal - $discount), 2),
            'coupon_code' => $discount > 0 ? $couponCode : null,
            'breakdown' => [
                'base' => round($unitPrice * $quantity, 2),
                'options' => $optionAdjustments['items'],
                'rules' => $ruleAdjustments['items'],
                'setup_fee' => round($setupFee, 2),
                'discount' => round($discount, 2),
            ],
        ];
    }

    private function optionAdjustments(Product $product, array $selectedOptions, int $quantity): array
    {
        $items = [];
        $total = 0;

        $options = $product->customizationOptions()
            ->where('is_active', true)
            ->get()
            ->groupBy('option_type');

        foreach ($selectedOptions as $type => $value) {
            $values = is_array($value) ? $value : [$value];

            foreach ($values as $selectedValue) {
                $key = $this->optionKey($selectedValue);
                $group = $options->get($type);
                if (! $group) {
                    continue;
                }

                $match = $group->first(function ($option) use ($key, $selectedValue) {
                    return $option->option_key === $key || $option->label === $selectedValue;
                });

                if (! $match) {
                    continue;
                }

                $amount = round((float) $match->price_modifier * $quantity, 2);
                $total += $amount;
                $items[] = [
                    'type' => $type,
                    'key' => $match->option_key,
                    'label' => $match->label,
                    'amount' => $amount,
                ];
            }
        }

        return ['total' => $total, 'items' => $items];
    }

    private function ruleAdjustments(Product $product, array $selectedOptions, int $quantity, float $unitPrice): array
    {
        $items = [];
        $total = 0;

        $rules = $product->pricingRules()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($rules as $rule) {
            if ($rule->min_quantity !== null && $quantity < (int) $rule->min_quantity) {
                continue;
            }

            if ($rule->max_quantity !== null && $quantity > (int) $rule->max_quantity) {
                continue;
            }

            if ($rule->option_type && ! $this->selectedOptionMatches($selectedOptions, $rule->option_type, $rule->option_key)) {
                continue;
            }

            $basis = $rule->rule_type === 'quantity' ? $unitPrice * $quantity : $unitPrice;
            $amount = $rule->adjustment_type === 'percentage'
                ? round($basis * ((float) $rule->adjustment_value / 100), 2)
                : round((float) $rule->adjustment_value, 2);

            $total += $amount;
            $items[] = [
                'rule_type' => $rule->rule_type,
                'adjustment_type' => $rule->adjustment_type,
                'adjustment_value' => (float) $rule->adjustment_value,
                'amount' => $amount,
            ];
        }

        return ['total' => $total, 'items' => $items];
    }

    private function selectedOptionMatches(array $selectedOptions, string $type, ?string $key): bool
    {
        if (! array_key_exists($type, $selectedOptions)) {
            return false;
        }

        if (! $key) {
            return true;
        }

        $values = is_array($selectedOptions[$type]) ? $selectedOptions[$type] : [$selectedOptions[$type]];

        return collect($values)->contains(fn ($value) => $this->optionKey($value) === $key || $value === $key);
    }

    private function optionKey(mixed $value): string
    {
        return Str::of((string) $value)->lower()->slug('_')->toString();
    }

    private function customizationPayload(ProductCustomizationDraft $draft): array
    {
        return [
            'selected_options' => $draft->selected_options ?? [],
            'quantity' => $draft->quantity,
            'coupon_code' => $draft->coupon_code,
            'files' => $draft->files->map(fn ($file) => [
                'path' => $file->file_path,
                'original_name' => $file->original_name,
                'file_type' => $file->file_type,
            ])->values()->all(),
        ];
    }

    private function sessionId(Request $request): string
    {
        return $request->header('X-Session-Id')
            ?: $request->header('X-Guest-Session')
            ?: (string) Str::uuid();
    }
}
