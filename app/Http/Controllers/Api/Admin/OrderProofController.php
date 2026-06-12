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
            'title' => 'nullable|string|max:255',
            'file' => 'required|file|max:10240', // 10MB max
            'preview_file' => 'nullable|file|max:10240',
            'expires_at' => 'nullable|date',
            'metadata' => 'nullable|array',
        ]);

        $filePath = $request->file('file')->store('order-proofs', 'public');
        $previewFilePath = $request->hasFile('preview_file')
            ? $request->file('preview_file')->store('order-proofs/previews', 'public')
            : null;

        $proof = EcommerceOrderProof::create([
            'order_id' => $request->order_id,
            'proof_type' => $request->proof_type,
            'title' => $request->title,
            'file_path' => $filePath,
            'preview_file_path' => $previewFilePath,
            'status' => 'awaiting_approval',
            'expires_at' => $request->expires_at,
            'metadata' => $request->metadata ?? [],
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
            'status' => 'required|in:awaiting_approval,approved,rejected,revision_requested',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $attributes = [
            'status' => $request->status,
            'reviewed_at' => now(),
        ];

        if ($request->status === 'approved') {
            $attributes['approved_at'] = now();
            $attributes['rejected_at'] = null;
            $attributes['rejection_reason'] = null;
        } elseif (in_array($request->status, ['rejected', 'revision_requested'], true)) {
            $attributes['approved_at'] = null;
            $attributes['rejected_at'] = $request->status === 'rejected' ? now() : null;
            $attributes['rejection_reason'] = $request->rejection_reason;
        } else {
            $attributes['approved_at'] = null;
            $attributes['rejected_at'] = null;
            $attributes['rejection_reason'] = null;
        }

        $orderProof->update($attributes);

        return response()->json($orderProof);
    }

    public function destroy(EcommerceOrderProof $orderProof)
    {
        $orderProof->delete();
        return response()->json(['success' => true]);
    }
}
