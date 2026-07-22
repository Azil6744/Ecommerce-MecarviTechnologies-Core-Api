<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceCart;
use App\Models\EcommerceCartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function activeCartFor(Request $request): ?EcommerceCart
    {
        $user = $request->user();

        if (!$user) {
            $sessionId = $request->header('X-Session-ID') ?: $request->header('X-Guest-Session-ID');
            if ($sessionId) {
                return EcommerceCart::firstOrCreate(
                    ['session_id' => $sessionId, 'status' => 'active'],
                    ['total_amount' => 0]
                );
            }
            return null;
        }

        return EcommerceCart::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'active'],
            ['total_amount' => 0]
        );
    }

    private function refreshCartTotals(EcommerceCart $cart): EcommerceCart
    {
        $total = $cart->items()->sum('total_price');
        $cart->forceFill(['total_amount' => $total])->save();

        return $cart->load('items.product');
    }

    /**
     * Get user's cart
     */
    public function index(Request $request)
    {
        $cart = $this->activeCartFor($request);
        if (!$cart) {
            return response()->json(['items' => [], 'total_amount' => 0]);
        }

        return response()->json($cart->load('items.product'));
    }

    /**
     * Add item to cart
     */
    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'attributes' => 'nullable|array',
            'options' => 'nullable|array',
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = (int) $request->quantity;
        $unitPrice = (float) ($product->sale_price ?? $product->price ?? 0);
        $options = $request->input('options', $request->input('attributes', []));

        $cart = $this->activeCartFor($request);

        $normalizedOptions = is_array($options) ? $options : [];
        ksort($normalizedOptions);

        $cartItem = $cart->items()
            ->where('product_id', $product->id)
            ->get()
            ->first(function ($item) use ($normalizedOptions) {
                $itemOptions = is_array($item->options) ? $item->options : [];
                ksort($itemOptions);
                return $itemOptions === $normalizedOptions;
            });

        if ($cartItem) {
            $nextQuantity = $cartItem->quantity + $quantity;
            $cartItem->update([
                'quantity' => $nextQuantity,
                'unit_price' => $unitPrice,
                'total_price' => round($unitPrice * $nextQuantity, 2),
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => round($unitPrice * $quantity, 2),
                'options' => $options,
            ]);
        }

        return response()->json($this->refreshCartTotals($cart), 201);
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $cartItem = EcommerceCartItem::whereHas('cart', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($itemId);

        $quantity = (int) $request->quantity;
        $cartItem->update([
            'quantity' => $quantity,
            'total_price' => round((float) $cartItem->unit_price * $quantity, 2),
        ]);

        $this->refreshCartTotals($cartItem->cart);

        return response()->json($cartItem->load('product'));
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, $itemId)
    {
        $user = $request->user();
        $cartItem = EcommerceCartItem::whereHas('cart', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($itemId);

        $cartItem->delete();

        $cart = EcommerceCart::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        return response()->json($cart ? $this->refreshCartTotals($cart) : null);
    }

    /**
     * Clear entire cart
     */
    public function clear(Request $request)
    {
        $user = $request->user();
        $cart = EcommerceCart::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($cart) {
            $cart->items()->delete();
            $cart->forceFill(['total_amount' => 0])->save();
        }

        return response()->json(['message' => 'Cart cleared successfully']);
    }
}
