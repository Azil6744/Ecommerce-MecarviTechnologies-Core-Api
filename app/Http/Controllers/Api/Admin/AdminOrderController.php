<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceOrder;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    /**
     * Display all orders (admin only)
     */
    public function index(Request $request)
    {
        $query = EcommerceOrder::with('user');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhere('user_id', 'like', '%' . $request->search . '%');
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->pagination($request->get('per_page', 15));

        return response()->json($orders);
    }

    /**
     * Get order details
     */
    public function show(EcommerceOrder $order)
    {
        return response()->json($order->load('user', 'items'));
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, EcommerceOrder $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled,refunded',
        ]);

        $order->update(['status' => $request->status]);

        return response()->json($order);
    }

    /**
     * Delete order
     */
    public function destroy(EcommerceOrder $order)
    {
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully']);
    }
}