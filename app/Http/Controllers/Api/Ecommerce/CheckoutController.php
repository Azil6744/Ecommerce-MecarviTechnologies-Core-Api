<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\EcommerceOrder;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    /**
     * Process the checkout request and generate an order.
     */
    public function process(Request $request)
    {
        // Basic validation
        $request->validate([
            'items' => 'required|array|min:1',
            'shipping_method' => 'string|nullable',
            'payment_method' => 'required|string',
            'total_amount' => 'required|numeric'
        ]);

        try {
            DB::beginTransaction();

            // Create Order
            $orderData = [
                'user_id' => $request->user()->id ?? null,
                'status' => 'pending',
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method,
                'shipping_method' => $request->shipping_method,
                // store items as JSON if a dedicated table doesn't exist
                'items' => json_encode($request->items),
                'order_number' => 'ORD-' . strtoupper(uniqid()),
            ];

            $order = EcommerceOrder::create($orderData);

            // Here we would typically clear the user's cart:
            // CartItem::where('user_id', $request->user()->id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully.',
                'order' => $order
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process checkout: ' . $e->getMessage()
            ], 500);
        }
    }
}
