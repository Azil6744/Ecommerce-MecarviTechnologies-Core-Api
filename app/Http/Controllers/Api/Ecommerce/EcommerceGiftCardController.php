<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceGiftCard;
use Illuminate\Support\Facades\Schema;

class EcommerceGiftCardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        // Check if admin to return all, or just user
        if ($user && $user->isSuperAdmin()) {
            return response()->json(['success' => true, 'data' => EcommerceGiftCard::all()]);
        }
        
        // Get by user_id if column exists, otherwise all
        if(Schema::hasColumn((new EcommerceGiftCard)->getTable(), 'user_id')) {
            $query = EcommerceGiftCard::where('user_id', $user->id);
            return response()->json(['success' => true, 'data' => $query->get()]);
        }

        return response()->json(['success' => true, 'data' => EcommerceGiftCard::all()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:ecommerce_gift_cards,code',
            'recipient_name' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $item = EcommerceGiftCard::create([
            'code' => $request->code,
            'recipient_name' => $request->recipient_name,
            'recipient_email' => $request->recipient_email ?? '',
            'sender_name' => $request->sender_name ?? null,
            'initial_balance' => $request->amount,
            'current_balance' => $request->amount,
            'status' => $request->status ?? 'active',
            'expires_at' => $request->expires_at ?? null,
        ]);

        return response()->json(['success' => true, 'data' => $item]);
    }

    public function show(Request $request, $id)
    {
        $item = EcommerceGiftCard::findOrFail($id);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = EcommerceGiftCard::findOrFail($id);
        $item->update($request->all());
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy(Request $request, $id)
    {
        $item = EcommerceGiftCard::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }
}
