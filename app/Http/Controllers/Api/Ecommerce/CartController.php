<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceCart;
use App\Models\EcommerceCartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get user's cart
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $cart = EcommerceCart::where('user_id', $user->id)->with('items.product')->first();

        if (!$cart) {
            $cart = EcommerceCart::create(['user_id' => $user->id]);
        }

        return response()->json($cart);
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
        ]);

        $user = $request->user();
        $product = Product::findOrFail($request->product_id);

        $cart = EcommerceCart::firstOrCreate(['user_id' => $user->id]);

        // Check if item already in cart
        $cartItem = $cart->items()->where('product_id', $product->id)->first();

        if ($cartItem) {
            $cartItem->update(['quantity' => $cartItem->quantity + $request->quantity]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->sale_price ?? $product->price,
                'attributes' => $request->attributes,
            ]);
        }

        return response()->json($cart->load('items.product'), 201);
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

        $cartItem->update(['quantity' => $request->quantity]);

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

        $cart = EcommerceCart::where('user_id', $user->id)->with('items.product')->first();
        return response()->json($cart);
    }

    /**
     * Clear entire cart
     */
    public function clear(Request $request)
    {
        $user = $request->user();
        $cart = EcommerceCart::where('user_id', $user->id)->first();

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json(['message' => 'Cart cleared successfully']);
    }
}
