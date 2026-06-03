<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceReview;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    /**
     * Get all reviews with approval management
     */
    public function index(Request $request)
    {
        $query = EcommerceReview::with('product', 'user');

        if ($request->filled('status')) {
            $query->whereRaw('LOWER(status) = ?', [strtolower($request->status)]);
        }

        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->paginate($request->get('per_page', 15));

        return response()->json($reviews);
    }

    /**
     * Show review details
     */
    public function show(EcommerceReview $review)
    {
        return response()->json($review->load('product', 'user'));
    }

    /**
     * Approve/reject review
     */
    public function approve(Request $request, EcommerceReview $review)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $review->update(['status' => $request->status]);

        return response()->json($review);
    }

    /**
     * Delete review
     */
    public function destroy(EcommerceReview $review)
    {
        $review->delete();
        return response()->json(['message' => 'Review deleted successfully']);
    }
}
