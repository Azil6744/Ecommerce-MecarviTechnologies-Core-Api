<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceCoupon;
use App\Models\Product;
use App\Models\ProductCustomizationOption;
use App\Models\ProductPreviewAsset;
use App\Models\ProductPricingRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductDetailDataController extends Controller
{
    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'data' => $product->load([
                'previewAssets',
                'customizationOptions',
                'pricingRules',
                'coupons',
                'relatedProducts.previewAssets',
                'relatedProducts.category',
            ]),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'preview_assets' => ['nullable', 'array'],
            'preview_assets.*.side' => ['required_with:preview_assets', 'string', 'max:50'],
            'preview_assets.*.image_path' => ['required_with:preview_assets', 'string'],
            'preview_assets.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'preview_assets.*.is_active' => ['nullable', 'boolean'],
            'preview_assets.*.metadata' => ['nullable', 'array'],

            'customization_options' => ['nullable', 'array'],
            'customization_options.*.option_type' => ['required_with:customization_options', 'string', 'max:100'],
            'customization_options.*.option_key' => ['required_with:customization_options', 'string', 'max:100'],
            'customization_options.*.label' => ['required_with:customization_options', 'string', 'max:255'],
            'customization_options.*.price_modifier' => ['nullable', 'numeric'],
            'customization_options.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'customization_options.*.is_active' => ['nullable', 'boolean'],
            'customization_options.*.metadata' => ['nullable', 'array'],

            'pricing_rules' => ['nullable', 'array'],
            'pricing_rules.*.rule_type' => ['nullable', 'string', 'max:100'],
            'pricing_rules.*.min_quantity' => ['nullable', 'integer', 'min:1'],
            'pricing_rules.*.max_quantity' => ['nullable', 'integer', 'min:1'],
            'pricing_rules.*.option_type' => ['nullable', 'string', 'max:100'],
            'pricing_rules.*.option_key' => ['nullable', 'string', 'max:100'],
            'pricing_rules.*.adjustment_type' => ['nullable', 'string', 'in:fixed,percentage'],
            'pricing_rules.*.adjustment_value' => ['nullable', 'numeric'],
            'pricing_rules.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'pricing_rules.*.is_active' => ['nullable', 'boolean'],
            'pricing_rules.*.metadata' => ['nullable', 'array'],

            'related_products' => ['nullable', 'array'],
            'related_products.*.related_product_id' => ['required_with:related_products', 'integer', 'exists:products,id'],
            'related_products.*.relation_type' => ['nullable', 'string', 'max:100'],
            'related_products.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'coupons' => ['nullable', 'array'],
            'coupons.*.code' => ['required_with:coupons', 'string', 'max:100'],
            'coupons.*.title' => ['required_with:coupons', 'string', 'max:255'],
            'coupons.*.subtitle' => ['nullable', 'string', 'max:255'],
            'coupons.*.discount_type' => ['nullable', 'string', 'in:fixed,percentage,free_shipping,buy_x_get_y'],
            'coupons.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'coupons.*.min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'coupons.*.usage_limit' => ['nullable', 'integer', 'min:1'],
            'coupons.*.used_count' => ['nullable', 'integer', 'min:0'],
            'coupons.*.starts_at' => ['nullable', 'date'],
            'coupons.*.expires_at' => ['nullable', 'date'],
            'coupons.*.is_active' => ['nullable', 'boolean'],
            'coupons.*.metadata' => ['nullable', 'array'],
            'coupons.*.metadata.notes' => ['nullable', 'string', 'max:2000'],
            'coupons.*.metadata.note' => ['nullable', 'string', 'max:255'],
            'coupons.*.metadata.side' => ['nullable', 'string', 'max:50'],
            'coupons.*.metadata.buy_quantity' => ['nullable', 'integer', 'min:1'],
            'coupons.*.metadata.get_quantity' => ['nullable', 'integer', 'min:1'],
            'coupons.*.metadata.reward_amount' => ['nullable', 'numeric', 'min:0'],
            'coupons.*.metadata.discount_value' => ['nullable', 'numeric', 'min:0'],
            'coupons.*.metadata.max_order_amount' => ['nullable', 'numeric', 'min:0'],
            'coupons.*.metadata.max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'coupons.*.metadata.per_customer_limit' => ['nullable', 'integer', 'min:1'],
            'coupons.*.metadata.customer_eligibility' => ['nullable', 'string', 'max:100'],
            'coupons.*.metadata.apply_scope' => ['nullable', 'string', 'in:all_products,specific_products,specific_categories,specific_services,specific_customers,membership_plans'],
            'coupons.*.metadata.apply_method' => ['nullable', 'string', 'in:code,auto'],
            'coupons.*.metadata.stacking_rule' => ['nullable', 'string', 'max:100'],
            'coupons.*.metadata.allow_with_store_credit' => ['nullable', 'boolean'],
            'coupons.*.metadata.allow_with_gift_card' => ['nullable', 'boolean'],
            'coupons.*.metadata.allow_with_loyalty_points' => ['nullable', 'boolean'],
            'coupons.*.metadata.exclude_sale_items' => ['nullable', 'boolean'],
            'coupons.*.metadata.exclude_selected_products' => ['nullable', 'boolean'],
            'coupons.*.metadata.exclude_selected_categories' => ['nullable', 'boolean'],
            'coupons.*.metadata.exclude_gift_cards' => ['nullable', 'boolean'],
            'coupons.*.metadata.exclude_shipping' => ['nullable', 'boolean'],
            'coupons.*.metadata.exclude_taxes' => ['nullable', 'boolean'],
            'coupons.*.metadata.shipping_scope' => ['nullable', 'string', 'in:all,specific'],
            'coupons.*.metadata.max_shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'coupons.*.metadata.get_discount_type' => ['nullable', 'string', 'in:free,percentage'],
            'coupons.*.metadata.get_discount_value' => ['nullable', 'numeric', 'min:0'],
            'coupons.*.metadata.buy_applies_to' => ['nullable', 'string', 'in:same,specific'],
        ]);

        DB::transaction(function () use ($product, $validated) {
            if (array_key_exists('preview_assets', $validated)) {
                $product->previewAssets()->delete();
                foreach ($validated['preview_assets'] ?? [] as $asset) {
                    ProductPreviewAsset::create([
                        'product_id' => $product->id,
                        'side' => $asset['side'],
                        'image_path' => $asset['image_path'],
                        'sort_order' => $asset['sort_order'] ?? 0,
                        'is_active' => $asset['is_active'] ?? true,
                        'metadata' => $asset['metadata'] ?? [],
                    ]);
                }
            }

            if (array_key_exists('customization_options', $validated)) {
                $product->customizationOptions()->delete();
                foreach ($validated['customization_options'] ?? [] as $option) {
                    ProductCustomizationOption::create([
                        'product_id' => $product->id,
                        'option_type' => $option['option_type'],
                        'option_key' => $option['option_key'],
                        'label' => $option['label'],
                        'price_modifier' => $option['price_modifier'] ?? 0,
                        'sort_order' => $option['sort_order'] ?? 0,
                        'is_active' => $option['is_active'] ?? true,
                        'metadata' => $option['metadata'] ?? [],
                    ]);
                }
            }

            if (array_key_exists('pricing_rules', $validated)) {
                $product->pricingRules()->delete();
                foreach ($validated['pricing_rules'] ?? [] as $rule) {
                    ProductPricingRule::create([
                        'product_id' => $product->id,
                        'rule_type' => $rule['rule_type'] ?? 'quantity',
                        'min_quantity' => $rule['min_quantity'] ?? null,
                        'max_quantity' => $rule['max_quantity'] ?? null,
                        'option_type' => $rule['option_type'] ?? null,
                        'option_key' => $rule['option_key'] ?? null,
                        'adjustment_type' => $rule['adjustment_type'] ?? 'fixed',
                        'adjustment_value' => $rule['adjustment_value'] ?? 0,
                        'sort_order' => $rule['sort_order'] ?? 0,
                        'is_active' => $rule['is_active'] ?? true,
                        'metadata' => $rule['metadata'] ?? [],
                    ]);
                }
            }

            if (array_key_exists('related_products', $validated)) {
                DB::table('product_related_products')->where('product_id', $product->id)->delete();
                foreach ($validated['related_products'] ?? [] as $relation) {
                    DB::table('product_related_products')->insert([
                        'product_id' => $product->id,
                        'related_product_id' => $relation['related_product_id'],
                        'relation_type' => $relation['relation_type'] ?? 'related',
                        'sort_order' => $relation['sort_order'] ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if (array_key_exists('coupons', $validated)) {
                $couponIds = [];
                foreach ($validated['coupons'] ?? [] as $couponData) {
                    $coupon = EcommerceCoupon::updateOrCreate(
                        ['code' => strtoupper($couponData['code'])],
                        [
                            'title' => $couponData['title'],
                            'subtitle' => $couponData['subtitle'] ?? null,
                            'discount_type' => $couponData['discount_type'] ?? 'fixed',
                            'discount_value' => $couponData['discount_value'] ?? 0,
                            'min_order_amount' => $couponData['min_order_amount'] ?? 0,
                            'usage_limit' => $couponData['usage_limit'] ?? null,
                            'used_count' => $couponData['used_count'] ?? 0,
                            'starts_at' => $couponData['starts_at'] ?? null,
                            'expires_at' => $couponData['expires_at'] ?? null,
                            'is_active' => $couponData['is_active'] ?? true,
                            'metadata' => $couponData['metadata'] ?? [],
                        ]
                    );

                    $couponIds[] = $coupon->id;
                }

                $product->coupons()->sync($couponIds);
            }
        });

        return $this->show($product->fresh());
    }
}
