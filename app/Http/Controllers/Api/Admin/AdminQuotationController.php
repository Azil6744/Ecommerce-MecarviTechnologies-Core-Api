<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceQuotation;
use Illuminate\Http\Request;

class AdminQuotationController extends Controller
{
    /**
     * Get all quotations
     */
    public function index(Request $request)
    {
        $query = EcommerceQuotation::with('user');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $quotations = $query->paginate($request->get('per_page', 15));

        return response()->json($quotations);
    }

    /**
     * Show quotation details
     */
    public function show(EcommerceQuotation $quotation)
    {
        return response()->json($quotation->load('user'));
    }

    /**
     * Update quotation status
     */
    public function updateStatus(Request $request, EcommerceQuotation $quotation)
    {
        $request->validate([
            'status' => 'required|string|in:pending,quoted,accepted,approved,rejected,declined,revision_requested,expired',
        ]);

        $status = strtolower($request->status);
        if ($status === 'approved') {
            $status = 'accepted';
        }
        if ($status === 'rejected') {
            $status = 'declined';
        }

        $quotation->update(['status' => $status]);

        return response()->json($quotation);
    }

    /**
     * Send quote response to user
     */
    public function sendQuote(Request $request, EcommerceQuotation $quotation)
    {
        $request->validate([
            'quote_price' => 'required|numeric|min:0',
            'quote_details' => 'nullable|string',
        ]);

        $quotation->update([
            'quote_price' => $request->quote_price,
            'quote_details' => $request->quote_details,
            'status' => 'quoted',
            'quoted_at' => now(),
        ]);

        // TODO: Send email notification to user

        return response()->json($quotation);
    }
}