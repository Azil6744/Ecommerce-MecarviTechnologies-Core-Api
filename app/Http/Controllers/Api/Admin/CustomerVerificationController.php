<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceCustomerVerification;
use Illuminate\Http\Request;

class CustomerVerificationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $query = EcommerceCustomerVerification::with('user')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $verifications = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $verifications
        ]);
    }

    public function show($id)
    {
        $verification = EcommerceCustomerVerification::with('user')->find($id);

        if (!$verification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Verification not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $verification
        ]);
    }

    public function approve($id)
    {
        $verification = EcommerceCustomerVerification::find($id);

        if (!$verification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Verification not found'
            ], 404);
        }

        $verification->status = 'approved';
        $verification->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer verification approved successfully.',
            'data' => $verification
        ]);
    }

    public function reject(Request $request, $id)
    {
        $verification = EcommerceCustomerVerification::find($id);

        if (!$verification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Verification not found'
            ], 404);
        }

        $request->validate([
            'notes' => 'nullable|string'
        ]);

        $verification->status = 'rejected';
        if ($request->has('notes')) {
            $verification->notes = $request->notes;
        }
        $verification->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer verification rejected successfully.',
            'data' => $verification
        ]);
    }
}
