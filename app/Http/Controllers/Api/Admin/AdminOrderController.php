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
                ->paginate($request->get('per_page', 20));

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
            $order = EcommerceOrder::with(['user', 'items.product', 'proofs', 'verifications'])->findOrFail($id);
            return response()->json(['success' => true, 'data' => ['order' => $order]]);
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
                'status' => 'required|in:pending,processing,completed,cancelled,refunded',
            ]);

            $order = EcommerceOrder::findOrFail($id);
            $order->update(['status' => $request->status]);
            return response()->json(['success' => true, 'message' => 'Order status updated.', 'data' => ['order' => $order]]);
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
}
