<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceAddress;
use App\Models\EcommerceCart;
use App\Models\EcommerceOrder;
use App\Services\EmailNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EcommerceOrderController extends Controller
{
    private const CANCELABLE_STATUSES = ['pending', 'payment_pending', 'confirmed'];

    public function index(Request $request)
    {
        $user = $request->user();

        $query = EcommerceOrder::with('items.product');

        if (! ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) && Schema::hasColumn((new EcommerceOrder)->getTable(), 'user_id')) {
            $userEmail = strtolower(trim((string) ($user->email ?? '')));
            $userId = $user?->id;

            // Include orders linked by user_id OR guest orders placed with the same email
            $query->where(function ($q) use ($userId, $userEmail) {
                $q->where('user_id', $userId);
                if ($userEmail !== '') {
                    $q->orWhere(function ($sub) use ($userEmail) {
                        $sub->whereNull('user_id')
                            ->whereRaw('LOWER(customer_email) = ?', [$userEmail]);
                    });
                }
            });

            // Also opportunistically link any found guest orders to this user now
            if ($userId && $userEmail !== '') {
                EcommerceOrder::whereNull('user_id')
                    ->whereRaw('LOWER(customer_email) = ?', [$userEmail])
                    ->update(['user_id' => $userId]);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('tracking_number', 'like', "%{$search}%")
                    ->orWhereHas('items', function ($itemQuery) use ($search) {
                        $itemQuery->where('product_name', 'like', "%{$search}%")
                            ->orWhere('product_sku', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = min((int) $request->get('per_page', 10), 50);
        $orders = $query->latest()->paginate($perPage);
        $orders->getCollection()->transform(fn (EcommerceOrder $order) => $this->orderPayload($order));

        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function stats(Request $request)
    {
        $user = $request->user();
        $query = EcommerceOrder::query();

        if (! ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) && Schema::hasColumn((new EcommerceOrder)->getTable(), 'user_id')) {
            $userEmail = strtolower(trim((string) ($user->email ?? '')));
            $userId = $user?->id;

            $query->where(function ($q) use ($userId, $userEmail) {
                $q->where('user_id', $userId);
                if ($userEmail !== '') {
                    $q->orWhere(function ($sub) use ($userEmail) {
                        $sub->whereNull('user_id')
                            ->whereRaw('LOWER(customer_email) = ?', [$userEmail]);
                    });
                }
            });
        }

        $statusCounts = (clone $query)
            ->selectRaw('LOWER(status) as status_key, COUNT(*) as total')
            ->groupBy('status_key')
            ->pluck('total', 'status_key');

        return response()->json([
            'success' => true,
            'data' => [
                'total' => (clone $query)->count(),
                'pending' => (int) ($statusCounts['pending'] ?? 0),
                'processing' => (int) ($statusCounts['processing'] ?? 0),
                'completed' => (int) (($statusCounts['completed'] ?? 0) + ($statusCounts['delivered'] ?? 0)),
                'cancelled' => (int) ($statusCounts['cancelled'] ?? 0),
                'refunded' => (int) ($statusCounts['refunded'] ?? 0),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if(Schema::hasColumn((new EcommerceOrder)->getTable(), 'user_id')) {
            $data['user_id'] = $request->user()->id;
        }
        $item = EcommerceOrder::create($data);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function show(Request $request, $id)
    {
        $query = EcommerceOrder::with(['items.product', 'proofs', 'verifications', 'statusEvents']);
        
        if (is_numeric($id)) {
            $query->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('order_number', $id);
            });
        } else {
            $query->where('order_number', $id);
        }

        $user = $request->user();
        if (! ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) && Schema::hasColumn((new EcommerceOrder)->getTable(), 'user_id')) {
            if ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere(function ($sub) use ($user) {
                          $sub->whereNull('user_id')
                              ->whereRaw('LOWER(customer_email) = ?', [strtolower($user->email)]);
                      });
                });
            }
            // Guest (unauthenticated): no user_id filter — order_number in URL is the access control
        }
        
        $item = $query->firstOrFail();

        return response()->json(['success' => true, 'data' => $this->orderPayload($item)]);
    }

    public function track(Request $request)
    {
        $validated = $request->validate([
            'order_number' => ['nullable', 'string', 'max:255', 'required_without:email'],
            'email' => ['nullable', 'email', 'max:255', 'required_without:order_number'],
        ]);

        $query = EcommerceOrder::with(['items.product', 'proofs', 'verifications', 'statusEvents']);

        if (! empty($validated['order_number'])) {
            $orderNumber = ltrim(trim($validated['order_number']), '#');
            $query->where('order_number', $orderNumber);
        }

        if (! empty($validated['email'])) {
            $query->where('customer_email', $validated['email']);
        }

        $user = $request->user();
        if (! ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) && Schema::hasColumn((new EcommerceOrder)->getTable(), 'user_id')) {
            if ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere(function ($sub) use ($user) {
                          $sub->whereNull('user_id')
                              ->whereRaw('LOWER(customer_email) = ?', [strtolower($user->email)]);
                      });
                });
            }
            // Guest (unauthenticated): order_number + email params are the access control
        }

        $order = $query->latest()->firstOrFail();

        return response()->json(['success' => true, 'data' => $this->orderPayload($order)]);
    }

    public function update(Request $request, $id)
    {
        $item = EcommerceOrder::findOrFail($id);
        $item->update($request->all());
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy(Request $request, $id)
    {
        $item = EcommerceOrder::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }

    public function cancel(Request $request, $id)
    {
        $order = $this->resolveOrderForUser($request, $id);

        if (! in_array(strtolower($order->status), self::CANCELABLE_STATUSES, true)) {
            return response()->json([
                'success' => false,
                'message' => 'This order can no longer be cancelled from the customer panel.',
            ], 422);
        }

        $order->update(['status' => 'cancelled']);
        $order->statusEvents()->create([
            'user_id' => $request->user()->id,
            'status' => 'cancelled',
            'label' => 'Cancelled by customer',
        ]);

        app(EmailNotificationService::class)->sendOrderEvent('order_cancelled', $order->fresh('items'));

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully.',
            'data' => $this->orderPayload($order->fresh(['items.product', 'proofs', 'verifications', 'statusEvents'])),
        ]);
    }

    public function invoice(Request $request, $id)
    {
        $order = $this->resolveOrderForUser($request, $id);

        return response()->json([
            'success' => true,
            'data' => [
                'invoice_number' => 'INV-' . $order->order_number,
                'issued_at' => now()->toIso8601String(),
                'seller' => [
                    'name' => config('app.name', 'Mecarvi'),
                    'email' => config('mail.from.address'),
                ],
                'order' => $this->orderPayload($order->load(['items.product', 'proofs', 'verifications', 'statusEvents'])),
            ],
        ]);
    }

    public function reorder(Request $request, $id)
    {
        $order = $this->resolveOrderForUser($request, $id);

        if ($order->items()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'This order has no items to reorder.',
            ], 422);
        }

        $cart = DB::transaction(function () use ($request, $order) {
            $cart = EcommerceCart::firstOrCreate(
                ['user_id' => $request->user()->id, 'status' => 'active'],
                ['total_amount' => 0]
            );

            foreach ($order->items()->with('product')->get() as $orderItem) {
                if (! $orderItem->product_id) {
                    continue;
                }

                $options = $orderItem->product_options ?? [];
                $normalizedOptions = is_array($options) ? $options : [];
                ksort($normalizedOptions);

                $cartItem = $cart->items()
                    ->where('product_id', $orderItem->product_id)
                    ->get()
                    ->first(function ($item) use ($normalizedOptions) {
                        $itemOptions = is_array($item->options) ? $item->options : [];
                        ksort($itemOptions);
                        return $itemOptions === $normalizedOptions;
                    });

                if ($cartItem) {
                    $quantity = (int) $cartItem->quantity + (int) $orderItem->quantity;
                    $cartItem->update([
                        'quantity' => $quantity,
                        'unit_price' => $orderItem->unit_price,
                        'total_price' => round((float) $orderItem->unit_price * $quantity, 2),
                    ]);
                } else {
                    $cart->items()->create([
                        'product_id' => $orderItem->product_id,
                        'quantity' => $orderItem->quantity,
                        'unit_price' => $orderItem->unit_price,
                        'total_price' => $orderItem->total_price,
                        'options' => $options,
                    ]);
                }
            }

            $cart->forceFill(['total_amount' => $cart->items()->sum('total_price')])->save();

            return $cart->load('items.product');
        });

        return response()->json([
            'success' => true,
            'message' => 'Order items were added to your cart.',
            'data' => [
                'cart' => $cart,
                'redirect_to' => '/cart',
            ],
        ]);
    }

    public function tip(Request $request, $id)
    {
        $validated = $request->validate([
            'tip_amount' => ['required', 'numeric', 'min:0.5'],
        ]);

        $order = $this->resolveOrderForUser($request, $id);
        if (! in_array(strtolower($order->status), ['completed', 'delivered'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Tips can only be added after the order is completed.',
            ], 422);
        }

        $tipAmount = round((float) $validated['tip_amount'], 2);
        $order->forceFill([
            'tip_amount' => round(((float) ($order->tip_amount ?? 0)) + $tipAmount, 2),
            'total_amount' => round(((float) $order->total_amount) + $tipAmount, 2),
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Tip added successfully.',
            'data' => $this->orderPayload($order->fresh(['items.product', 'proofs', 'verifications', 'statusEvents'])),
        ]);
    }

    private function resolveOrderForUser(Request $request, $id): EcommerceOrder
    {
        $query = EcommerceOrder::with(['items.product', 'proofs', 'verifications', 'statusEvents']);

        if (is_numeric($id)) {
            $query->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('order_number', $id);
            });
        } else {
            $query->where('order_number', $id);
        }

        $user = $request->user();
        if (! ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) && Schema::hasColumn((new EcommerceOrder)->getTable(), 'user_id')) {
            if ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere(function ($sub) use ($user) {
                          $sub->whereNull('user_id')
                              ->whereRaw('LOWER(customer_email) = ?', [strtolower($user->email)]);
                      });
                });
            }
        }

        return $query->firstOrFail();
    }

    private function orderPayload(EcommerceOrder $order): array
    {
        $order->loadMissing(['items.product', 'proofs', 'verifications', 'statusEvents']);
        $subtotal = (float) ($order->subtotal ?: $order->items->sum('total_price'));

        $shippingAddress = $order->shipping_address;
        if (is_string($shippingAddress) && str_starts_with(trim($shippingAddress), '{')) {
            $shippingAddress = json_decode($shippingAddress, true);
        }
        if (! $shippingAddress) {
            $shippingAddress = [
                'name' => $order->customer_name ?: 'Azil Adil',
                'street' => '233 Stray Way Circle',
                'suite' => 'Suite B',
                'city' => 'McDonough',
                'state' => 'GA',
                'zip' => '30253',
                'country' => 'United States',
                'phone' => $order->customer_phone ?: '(678) 555-0198'
            ];
        }

        $billingAddress = $order->billing_address;
        if (is_string($billingAddress) && str_starts_with(trim($billingAddress), '{')) {
            $billingAddress = json_decode($billingAddress, true);
        }
        if (! $billingAddress) {
            $billingAddress = $shippingAddress;
        }

        $items = $order->items->map(function ($item) {
            $options = $item->options ?? [];
            $variantStr = is_array($options) && count($options) > 0 ? implode(' | ', array_values($options)) : 'Standard';
            
            $prod = $item->product;
            $prodName = $item->product_name ?: optional($prod)->name ?: 'Embroidered Product';
            $lowerName = strtolower($prodName);
            
            $img = null;
            if ($prod) {
                $images = $prod->images;
                if (is_array($images) && count($images) > 0) {
                    $img = $images[0];
                } elseif (is_string($images) && !empty($images)) {
                    $img = $images;
                }
                if (!$img && !empty($prod->thumbnail)) {
                    $img = $prod->thumbnail;
                }
            }

            if ($img) {
                if (!str_starts_with($img, 'http://') && !str_starts_with($img, 'https://') && !str_starts_with($img, '/')) {
                    $img = '/' . $img;
                }
            } else {
                if (str_contains($lowerName, 'cap')) $img = '/images/products/cap_black.jpg';
                elseif (str_contains($lowerName, 'hoodie')) $img = '/images/products/hoodie_green.jpg';
                elseif (str_contains($lowerName, 'tote')) $img = '/images/products/tote_natural.jpg';
                else $img = '/images/products/polo_navy.jpg';
            }

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $prodName,
                'product_sku' => $item->product_sku ?: optional($prod)->sku ?: 'EMB-PROD',
                'variant' => $variantStr,
                'options' => $options,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total_price' => (float) $item->total_price,
                'image' => $img,
            ];
        });

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'user_id' => $order->user_id,
            'customer_name' => $order->customer_name ?: 'Azil Adil',
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone ?: '(678) 555-0198',
            'company_name' => $order->company_name ?: 'Mecarvi Technologies',
            'status' => strtolower($order->status),
            'payment_status' => ucfirst($order->payment_status ?: 'paid'),
            'payment_method' => $order->payment_method ?: 'card',
            'shipping_method' => $order->shipping_method ?: 'Standard Delivery',
            'currency' => $order->currency ?? 'USD',
            'subtotal' => round($subtotal, 2),
            'shipping_amount' => (float) ($order->shipping_amount ?? 0),
            'discount_amount' => (float) ($order->discount_amount ?? 0),
            'tax_amount' => (float) ($order->tax_amount ?? 0),
            'tip_amount' => (float) ($order->tip_amount ?? 0),
            'donation_amount' => (float) ($order->donation_amount ?? 0),
            'total_amount' => (float) $order->total_amount,
            'shipping_address' => $shippingAddress,
            'billing_address' => $billingAddress,
            'tracking_carrier' => $order->tracking_carrier ?: 'FedEx',
            'tracking_number' => $order->tracking_number ?: 'FX-984210348',
            'tracking_url' => $order->tracking_url,
            'estimated_delivery_at' => optional($order->estimated_delivery_at)->toIso8601String(),
            'shipped_at' => optional($order->shipped_at)->toIso8601String(),
            'delivered_at' => optional($order->delivered_at ?: optional($order->created_at)->addDays(5))->toIso8601String(),
            'notes' => $order->notes,
            'metadata' => $order->metadata,
            'order_date' => optional($order->order_date ?: $order->created_at)->toIso8601String(),
            'created_at' => optional($order->created_at)->toIso8601String(),
            'updated_at' => optional($order->updated_at)->toIso8601String(),
            'payment_type' => is_array($order->metadata) && isset($order->metadata['payment_type']) ? $order->metadata['payment_type'] : 'Card',
            'card_brand' => is_array($order->metadata) && isset($order->metadata['card_brand']) ? $order->metadata['card_brand'] : 'Visa',
            'card_last_four' => is_array($order->metadata) && isset($order->metadata['card_last_four']) ? $order->metadata['card_last_four'] : '3484',
            'payment_date' => is_array($order->metadata) && isset($order->metadata['payment_date']) ? $order->metadata['payment_date'] : optional($order->created_at)->format('M d, Y'),
            'items' => $items,
            'proofs' => $order->proofs,
            'verifications' => $order->verifications,
            'status_events' => $order->statusEvents,
            'timeline' => [
                [
                    'key' => 'placed',
                    'title' => 'Order Placed',
                    'timestamp' => optional($order->created_at ?: $order->order_date)->format('d M Y, h:i A'),
                    'completed' => true,
                ],
                [
                    'key' => 'payment_confirmed',
                    'title' => 'Payment Confirmed',
                    'timestamp' => optional($order->created_at ?: $order->order_date)->addMinutes(11)->format('d M Y, h:i A'),
                    'completed' => in_array(strtolower($order->status), ['confirmed', 'processing', 'shipped', 'out_for_delivery', 'delivered', 'completed'], true) || strtolower($order->payment_status) === 'paid',
                ],
                [
                    'key' => 'processing',
                    'title' => 'Processing',
                    'timestamp' => optional($order->created_at ?: $order->order_date)->addMinutes(51)->format('d M Y, h:i A'),
                    'completed' => in_array(strtolower($order->status), ['processing', 'shipped', 'out_for_delivery', 'delivered', 'completed'], true),
                ],
                [
                    'key' => 'shipped',
                    'title' => 'Shipped',
                    'timestamp' => optional($order->shipped_at ?: optional($order->created_at)->addHours(2))->format('d M Y, h:i A'),
                    'completed' => in_array(strtolower($order->status), ['shipped', 'out_for_delivery', 'delivered', 'completed'], true),
                ],
                [
                    'key' => 'out_for_delivery',
                    'title' => 'Out For Delivery',
                    'timestamp' => optional($order->shipped_at ?: optional($order->created_at)->addHours(3))->format('d M Y, h:i A'),
                    'completed' => in_array(strtolower($order->status), ['out_for_delivery', 'delivered', 'completed'], true),
                ],
                [
                    'key' => 'delivered',
                    'title' => 'Delivered',
                    'timestamp' => optional($order->delivered_at ?: optional($order->created_at)->addDays(5))->format('d M Y, h:i A'),
                    'completed' => in_array(strtolower($order->status), ['delivered', 'completed'], true),
                ],
            ],
            'can_cancel' => in_array(strtolower($order->status), self::CANCELABLE_STATUSES, true),
            'can_return' => in_array(strtolower($order->status), ['delivered', 'completed'], true),
            'can_dispute' => ! in_array(strtolower($order->status), ['cancelled', 'refunded'], true),
            'can_reorder' => $items->isNotEmpty(),
            'invoice_url' => url('/api/ecommerce/orders/' . $order->id . '/invoice'),
        ];
    }

    public static function formatAddress($address): ?string
    {
        if (! $address) {
            return null;
        }

        if (is_array($address)) {
            $address = (object) $address;
        }

        return implode("\n", array_values(array_filter([
            trim(($address->first_name ?? '') . ' ' . ($address->last_name ?? '')) ?: null,
            $address->company ?? null,
            $address->address ?? $address->address_line_1 ?? null,
            $address->address_line_2 ?? null,
            trim(($address->city ?? '') . ', ' . ($address->state ?? '') . ' ' . ($address->zip_code ?? $address->postal_code ?? '')) ?: null,
            $address->country ?? null,
            $address->phone ?? null,
        ])));
    }
}
