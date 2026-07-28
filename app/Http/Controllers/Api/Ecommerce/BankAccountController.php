<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceBankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index(Request $request)
    {
        $accounts = $request->user()
            ->bankAccounts()
            ->latest('is_default')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $accounts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'routing_number' => 'required|string|max:50',
            'account_number' => 'required|string|max:50',
            'is_default' => 'sometimes|boolean',
            'isDefault' => 'sometimes|boolean',
        ]);

        $accountNum = (string) $validated['account_number'];
        $last4 = substr($accountNum, -4);
        if (strlen($last4) < 4) {
            $last4 = str_pad($last4, 4, '0', STR_PAD_LEFT);
        }

        $payload = [
            'bank_name' => $validated['bank_name'],
            'account_holder_name' => $validated['account_holder_name'],
            'routing_number' => $validated['routing_number'],
            'account_number' => $accountNum, // raw or mask is fine for local database
            'last4' => $last4,
            'is_default' => (bool) ($validated['isDefault'] ?? $validated['is_default'] ?? false),
        ];

        if ($payload['is_default'] || ! $request->user()->bankAccounts()->exists()) {
            $request->user()->bankAccounts()->update(['is_default' => false]);
            $payload['is_default'] = true;
        }

        $account = $request->user()->bankAccounts()->create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Bank account linked successfully.',
            'data' => $account,
        ], 201);
    }

    public function destroy(Request $request, EcommerceBankAccount $bankAccount)
    {
        if ($bankAccount->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $wasDefault = (bool) $bankAccount->is_default;
        $bankAccount->delete();

        if ($wasDefault) {
            $nextAccount = $request->user()->bankAccounts()->latest()->first();
            if ($nextAccount) {
                $nextAccount->update(['is_default' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Bank account removed successfully.',
        ]);
    }
}
