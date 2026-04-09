<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceReturn;
use Illuminate\Http\Request;

class AdminReturnController extends Controller
{
    /**
     * Get all return requests
     */
    public function index(Request $request)
    {
        $query = EcommerceReturn::with('order', 'user');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $returns = $query->paginate($request->get('per_page', 15));

        return response()->json($returns);
    }

    /**
     * Show return details
     */
    public function show(EcommerceReturn $return)
    {
        return response()->json($return->load('order', 'user'));
    }

    /**
     * Update return status
     */
    public function updateStatus(Request $request, EcommerceReturn $return)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,completed,refunded',
        ]);

        $return->update(['status' => $request->status]);

        return response()->json($return);
    }

    /**
     * Process return approval and refund
     */
    public function approve(Request $request, EcommerceReturn $return)
    {
        $return->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // TODO: Process refund to user wallet

        return response()->json($return);
    }
}