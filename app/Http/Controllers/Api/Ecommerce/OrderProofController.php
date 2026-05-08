<?php

namespace App\Http\Controllers\Api\Ecommerce;

 horror;
use App\Http\Controllers\Controller;
use App\Models\EcommerceOrderProof;
use Illuminate\Http\Request;

class OrderProofController extends Controller
{
    /**
     * Get proofs for a specific order
     */
    public function getByOrder(Request $request, $orderId)
    {
        $user = $request->user();
        
        $proofs = EcommerceOrderProof::where('order_id', $orderId)
            ->whereHas('order', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $proofs
        ]);
    }

    /**
     * Approve a proof
     */
    public function approve(Request $request, $id)
    {
        $user = $request->user();
        
        $proof = EcommerceOrderProof::where('id', $id)
            ->whereHas('order', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->firstOrFail();

        $proof->update(['status' => 'approved']);

        return response()->json([
            'success' => true,
            'message' => 'Proof approved successfully.',
            'data' => $proof
        ]);
    }

    /**
     * Reject a proof
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $user = $request->user();
        
        $proof = EcommerceOrderProof::where('id', $id)
            ->whereHas('order', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->firstOrFail();

        $proof->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Proof rejected. We will update it soon.',
            'data' => $proof
        ]);
    }
}
