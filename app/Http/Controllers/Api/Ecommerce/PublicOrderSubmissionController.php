<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
            'page_context' => ['nullable', 'array'],
        ]);

        $product = Product::where('is_active', true)->findOrFail($validated['product_id']);
        $unitPrice = (float) ($product->sale_price ?? $product->price ?? 0);
        $quantity = (int) $validated['quantity'];
        $total = round($unitPrice * $quantity, 2);

        $order = EcommerceOrder::create([
            'order_number' => EcommerceOrder::generateOrderNumber(),
            'user_id' => optional($request->user())->id,
            'customer_name' => $validated['customer_name'],
            'company_name' => $validated['company_name'] ?? null,
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'metadata' => [
                'customization' => $validated['customization'] ?? [],
                'page_context' => $validated['page_context'] ?? [],
                'product_snapshot' => [
                    'category' => $product->category?->name,
                    'images' => $product->images ?? [],
                    'attributes' => $product->attributes ?? [],
                ],
            ],
            'total_amount' => $total,
            'order_date' => Carbon::today(),
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

        return response()->json([
            'success' => true,
            'message' => 'Order submitted successfully.',
            'data' => [
                'order' => $order->load('items'),
            ],
        ], 201);
    }
}
