<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceOrderProof;
use Illuminate\Http\Request;

class OrderProofController extends Controller
{
    public function index(Request $request)
    {
        $query = EcommerceOrderProof::with(['order', 'order.user']);

        if ($request->has('order_id')) {
            $query->where('order_id', $request->order_id);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $proofs = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($proofs);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:ecommerce_orders,id',
            'proof_type' => 'required|string',
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $filePath = $request->file('file')->store('order-proofs', 'public');

        $proof = EcommerceOrderProof::create([
            'order_id' => $request->order_id,
            'proof_type' => $request->proof_type,
            'file_path' => $filePath,
            'status' => 'awaiting_approval',
        ]);

        return response()->json([
            'success' => true,
            'data' => $proof
        ], 201);
    }

    public function show(EcommerceOrderProof $orderProof)
    {
        return response()->json($orderProof->load(['order', 'order.user']));
    }

    public function updateStatus(Request $request, EcommerceOrderProof $orderProof)
    {
        $request->validate([
            'status' => 'required|in:awaiting_approval,approved,rejected',
        ]);

        $orderProof->update(['status' => $request->status]);

        return response()->json($orderProof);
    }

    public function destroy(EcommerceOrderProof $orderProof)
    {
        $orderProof->delete();
        return response()->json(['success' => true]);
    }
}
