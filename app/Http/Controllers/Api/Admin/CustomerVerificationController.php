<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceCustomerVerification;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerVerificationController extends Controller
{
    public function sellerIndex(Request $request)
    {
        $request->merge(['type' => 'business']);

        return $this->index($request);
    }

    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $type = $request->query('type');
        $search = $request->query('search');
        $documentType = $request->query('document_type');

        $query = EcommerceCustomerVerification::with('user')->latest();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($documentType && $documentType !== 'all') {
            $query->where('document_type', 'like', '%' . $documentType . '%');
        }

        if ($type === 'business' || $type === 'seller') {
            $query->where(function ($q) {
                $q->where('document_type', 'like', '%seller%')
                    ->orWhere('document_type', 'like', '%business%')
                    ->orWhere('document_type', 'like', '%store%')
                    ->orWhere('document_type', 'like', '%license%')
                    ->orWhere('document_type', 'like', '%tax%')
                    ->orWhere('document_type', 'like', '%commercial%')
                    ->orWhereHas('user', function ($userQuery) {
                        $userQuery->where('role', 'seller')
                            ->orWhere('role', 'business')
                            ->orWhereHas('roles', function ($roleQuery) {
                                $roleQuery->whereIn('name', ['seller', 'business']);
                            });
                    });
            });
        } elseif ($type === 'customer' || $type === 'personal') {
            $query->where(function ($q) {
                $q->where('document_type', 'not like', '%license%')
                    ->where('document_type', 'not like', '%tax%')
                    ->where('document_type', 'not like', '%commercial%')
                    ->orWhereHas('user', function ($userQuery) {
                        $userQuery->where('role', 'customer');
                    });
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('document_type', 'like', '%' . $search . '%')
                    ->orWhere('notes', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%');
                    });
            });
        }

        $verifications = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $verifications
        ]);
    }

    public function stats(Request $request)
    {
        $type = $request->query('type');

        $baseQuery = EcommerceCustomerVerification::query();

        if ($type === 'business' || $type === 'seller') {
            $baseQuery->where(function ($q) {
                $q->where('document_type', 'like', '%seller%')
                    ->orWhere('document_type', 'like', '%business%')
                    ->orWhere('document_type', 'like', '%store%')
                    ->orWhere('document_type', 'like', '%license%')
                    ->orWhere('document_type', 'like', '%tax%')
                    ->orWhere('document_type', 'like', '%commercial%')
                    ->orWhereHas('user', function ($userQuery) {
                        $userQuery->where('role', 'seller')
                            ->orWhere('role', 'business');
                    });
            });
        }

        $total = (clone $baseQuery)->count();
        $pending = (clone $baseQuery)->where('status', 'pending')->count();
        $approved = (clone $baseQuery)->where('status', 'approved')->count();
        $rejected = (clone $baseQuery)->where('status', 'rejected')->count();
        $expired = (clone $baseQuery)->where('status', 'expired')->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total' => $total,
                'pending' => $pending,
                'approved' => $approved,
                'rejected' => $rejected,
                'expired' => $expired,
            ]
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
        $verification = EcommerceCustomerVerification::with('user')->find($id);

        if (!$verification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Verification not found'
            ], 404);
        }

        $verification->status = 'approved';
        $verification->save();

        if ($verification->user) {
            $verification->user->email_verified_at = now();
            $verification->user->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Verification approved successfully.',
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
            'message' => 'Verification rejected successfully.',
            'data' => $verification
        ]);
    }

    public function addNote(Request $request, $id)
    {
        $verification = EcommerceCustomerVerification::find($id);

        if (!$verification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Verification not found'
            ], 404);
        }

        $request->validate([
            'note' => 'required|string'
        ]);

        $existingNotes = $verification->notes ? $verification->notes . "\n" : '';
        $verification->notes = $existingNotes . '[' . now()->format('M d, Y H:i') . '] ' . $request->note;
        $verification->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Note added successfully.',
            'data' => $verification
        ]);
    }
}
