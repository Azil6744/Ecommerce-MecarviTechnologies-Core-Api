<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceWalletTransaction;
use App\Models\User;
use Illuminate\Http\Request;

class AdminWalletController extends Controller
{
    /**
     * Get all wallet transactions (admin view)
     */
    public function index(Request $request)
    {
        $query = EcommerceWalletTransaction::with('user');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        $transactions = $query->paginate($request->get('per_page', 15));

        return response()->json($transactions);
    }

    /**
     * Get user wallet balance and summary
     */
    public function getUserWallet(User $user)
    {
        $balance = $user->wallet_balance ?? 0;
        $transactions = $user->walletTransactions()->latest()->take(10)->get();

        return response()->json([
            'user_id' => $user->id,
            'balance' => $balance,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Add funds to user wallet (admin action)
     */
    public function creditWallet(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string',
        ]);

        $balance = ($user->wallet_balance ?? 0) + $request->amount;
        $user->update(['wallet_balance' => $balance]);

        EcommerceWalletTransaction::create([
            'user_id' => $user->id,
            'transaction_type' => 'credit',
            'amount' => $request->amount,
            'reason' => $request->reason,
            'balance_after' => $balance,
            'status' => 'completed',
        ]);

        return response()->json([
            'message' => 'Wallet credited successfully',
            'balance' => $balance,
        ]);
    }

    /**
     * Deduct funds from user wallet
     */
    public function debitWallet(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string',
        ]);

        $currentBalance = $user->wallet_balance ?? 0;

        if ($currentBalance < $request->amount) {
            return response()->json([
                'message' => 'Insufficient wallet balance',
            ], 422);
        }

        $balance = $currentBalance - $request->amount;
        $user->update(['wallet_balance' => $balance]);

        EcommerceWalletTransaction::create([
            'user_id' => $user->id,
            'transaction_type' => 'debit',
            'amount' => $request->amount,
            'reason' => $request->reason,
            'balance_after' => $balance,
            'status' => 'completed',
        ]);

        return response()->json([
            'message' => 'Amount deducted from wallet',
            'balance' => $balance,
        ]);
    }
}