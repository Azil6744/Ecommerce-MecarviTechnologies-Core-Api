<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceGiftCard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class EcommerceGiftCardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = EcommerceGiftCard::with(['order:id,order_number,total_amount,status,order_date', 'user:id,name,email']);

        if (! ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            if (Schema::hasColumn((new EcommerceGiftCard)->getTable(), 'user_id')) {
                $query->where('user_id', $user?->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('status')) {
            $query->whereRaw('LOWER(status) = ?', [strtolower((string) $request->status)]);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('recipient_email', 'like', "%{$search}%")
                    ->orWhere('sender_name', 'like', "%{$search}%");
            });
        }

        $cards = $query->latest()->get()->map(fn (EcommerceGiftCard $giftCard) => $this->giftCardPayload($giftCard));

        return response()->json(['success' => true, 'data' => $cards]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'nullable|string|unique:ecommerce_gift_cards,code',
            'recipient_name' => 'required|string',
            'recipient_email' => 'nullable|email',
            'amount' => 'required|numeric|min:0.01',
            'sender_name' => 'nullable|string',
            'status' => 'nullable|in:active,redeemed,expired,scheduled,cancelled,pending',
            'expires_at' => 'nullable|date',
            'delivery_type' => 'nullable|string|max:50',
            'message' => 'nullable|string',
            'scheduled_for' => 'nullable|date',
            'order_id' => 'nullable|integer',
            'currency' => 'nullable|string|max:10',
        ]);

        $payload = [
            'code' => $request->filled('code') ? $request->code : $this->generateUniqueGiftCardCode(),
            'recipient_name' => $request->recipient_name,
            'recipient_email' => $request->recipient_email ?? '',
            'sender_name' => $request->sender_name ?? null,
            'initial_balance' => $request->amount,
            'current_balance' => $request->amount,
            'status' => $this->normalizeStatus($request->input('status', 'active')),
            'expires_at' => $request->expires_at ?? null,
            'delivery_type' => $request->input('delivery_type'),
            'message' => $request->input('message'),
            'scheduled_for' => $request->input('scheduled_for'),
            'order_id' => $request->input('order_id'),
            'currency' => $request->input('currency', 'USD'),
            'purchased_at' => Carbon::now(),
        ];

        if (Schema::hasColumn((new EcommerceGiftCard)->getTable(), 'user_id') && $request->user()) {
            $payload['user_id'] = $request->user()->id;
        }

        $item = EcommerceGiftCard::create($payload);

        return response()->json(['success' => true, 'data' => $this->giftCardPayload($item->fresh(['order', 'user']))], 201);
    }

    public function show(Request $request, $id)
    {
        $item = $this->resolveGiftCard($request, $id)->load(['order:id,order_number,total_amount,status,order_date', 'user:id,name,email']);
        return response()->json(['success' => true, 'data' => $this->giftCardPayload($item)]);
    }

    public function update(Request $request, $id)
    {
        $item = $this->resolveGiftCard($request, $id);

        $data = $request->validate([
            'code' => 'sometimes|string|unique:ecommerce_gift_cards,code,' . $id,
            'recipient_name' => 'sometimes|string',
            'recipient_email' => 'sometimes|nullable|email',
            'sender_name' => 'sometimes|nullable|string',
            'initial_balance' => 'sometimes|numeric|min:0',
            'current_balance' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:active,redeemed,expired,scheduled,cancelled,pending',
            'expires_at' => 'sometimes|nullable|date',
            'delivery_type' => 'sometimes|nullable|string|max:50',
            'message' => 'sometimes|nullable|string',
            'scheduled_for' => 'sometimes|nullable|date',
            'redeemed_at' => 'sometimes|nullable|date',
            'currency' => 'sometimes|nullable|string|max:10',
        ]);

        if (isset($data['status'])) {
            $data['status'] = $this->normalizeStatus($data['status']);
        }

        if (array_key_exists('current_balance', $data) && ! array_key_exists('redeemed_at', $data)) {
            $data['redeemed_at'] = (float) $data['current_balance'] <= 0 ? Carbon::now() : null;
        }

        if (($data['status'] ?? null) === 'redeemed' && ! array_key_exists('redeemed_at', $data)) {
            $data['redeemed_at'] = Carbon::now();
        }

        $item->update($data);

        return response()->json(['success' => true, 'data' => $this->giftCardPayload($item->fresh(['order', 'user']))]);
    }

    public function destroy(Request $request, $id)
    {
        $item = $this->resolveGiftCard($request, $id);
        $item->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }

    private function resolveGiftCard(Request $request, int|string $id): EcommerceGiftCard
    {
        $query = EcommerceGiftCard::query();
        $user = $request->user();

        if (! ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) && Schema::hasColumn((new EcommerceGiftCard)->getTable(), 'user_id')) {
            $query->where('user_id', $user?->id);
        }

        return $query->findOrFail($id);
    }

    private function normalizeStatus(?string $status): string
    {
        $normalized = strtolower(trim((string) $status));
        return in_array($normalized, ['active', 'redeemed', 'expired', 'scheduled', 'cancelled', 'pending'], true)
            ? $normalized
            : 'active';
    }

    private function giftCardPayload(EcommerceGiftCard $giftCard): array
    {
        $initial = (float) $giftCard->initial_balance;
        $current = (float) $giftCard->current_balance;
        $redeemed = max($initial - $current, 0);

        return [
            'id' => $giftCard->id,
            'user_id' => $giftCard->user_id,
            'order_id' => $giftCard->order_id,
            'order_number' => $giftCard->order?->order_number,
            'code' => $giftCard->code,
            'recipient_name' => $giftCard->recipient_name,
            'recipient_email' => $giftCard->recipient_email,
            'sender_name' => $giftCard->sender_name,
            'initial_balance' => $initial,
            'current_balance' => $current,
            'redeemed_amount' => round($redeemed, 2),
            'status' => $this->normalizeStatus($giftCard->status),
            'expires_at' => $giftCard->expires_at ? $giftCard->expires_at->toDateString() : null,
            'delivery_type' => $giftCard->delivery_type,
            'message' => $giftCard->message,
            'scheduled_for' => $giftCard->scheduled_for ? $giftCard->scheduled_for->toIso8601String() : null,
            'purchased_at' => ($giftCard->purchased_at ?? $giftCard->created_at) ? ($giftCard->purchased_at ?? $giftCard->created_at)->toIso8601String() : null,
            'redeemed_at' => $giftCard->redeemed_at ? $giftCard->redeemed_at->toIso8601String() : null,
            'currency' => $giftCard->currency ?? 'USD',
            'created_at' => optional($giftCard->created_at)?->toIso8601String(),
            'updated_at' => optional($giftCard->updated_at)?->toIso8601String(),
            'user' => $giftCard->relationLoaded('user') ? $giftCard->user : null,
            'order' => $giftCard->relationLoaded('order') ? $giftCard->order : null,
        ];
    }

    private function generateUniqueGiftCardCode(): string
    {
        do {
            $code = 'GC-' . strtoupper(substr(bin2hex(random_bytes(6)), 0, 10));
        } while (EcommerceGiftCard::where('code', $code)->exists());

        return $code;
    }
}
