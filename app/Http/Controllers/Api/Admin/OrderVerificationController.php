<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceOrderVerification;
use Illuminate\Http\Request;

class OrderVerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = EcommerceOrderVerification::with(['order', 'order.user']);

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $verifications = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($verifications);
    }

    public function show(EcommerceOrderVerification $orderVerification)
    {
        return response()->json($orderVerification->load(['order', 'order.user']));
    }

    public function updateStatus(Request $request, EcommerceOrderVerification $orderVerification)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewing,cleared',
        ]);

        $orderVerification->update(['status' => $request->status]);

        return response()->json($orderVerification);
    }

    public function destroy(EcommerceOrderVerification $orderVerification)
    {
        $orderVerification->delete();
        return response()->json(['success' => true]);
    }
}
