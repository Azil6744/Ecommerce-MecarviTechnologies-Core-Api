<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceOrder;
use App\Models\EcommerceReturn;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EcommerceReturnController extends Controller
{
    private const ALLOWED_STATUSES = ['pending', 'approved', 'rejected', 'completed', 'refunded', 'cancelled'];

    public function index(Request $request)
    {
        $user = $request->user();

        $query = EcommerceReturn::query()
            ->with(['user', 'order.items.product'])
            ->latest();

        if (! ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->whereRaw('LOWER(status) = ?', [strtolower($request->string('status'))]);
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhere('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = max(1, min((int) $request->input('per_page', 10), 50));
        $returns = $query->paginate($perPage);
        $returns->getCollection()->transform(fn (EcommerceReturn $return) => $this->returnPayload($return));

        return response()->json(['success' => true, 'data' => $returns]);
    }

    public function stats(Request $request)
    {
        $user = $request->user();

        $query = EcommerceReturn::query();

        if (! ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            $query->where('user_id', $user->id);
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
                'completed' => (int) (($statusCounts['completed'] ?? 0) + ($statusCounts['refunded'] ?? 0)),
                'cancelled' => (int) (($statusCounts['cancelled'] ?? 0) + ($statusCounts['rejected'] ?? 0)),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['nullable', 'integer'],
            'order_number' => ['nullable', 'string', 'max:100'],
            'reason' => ['required', 'string', 'max:1000'],
            'return_items' => ['nullable', 'array'],
            'return_items.*.order_item_id' => ['nullable', 'integer'],
            'return_items.*.product_name' => ['nullable', 'string', 'max:255'],
            'return_items.*.product_sku' => ['nullable', 'string', 'max:255'],
            'return_items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'return_items.*.reason' => ['nullable', 'string', 'max:1000'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
            'refund_method' => ['nullable', 'string', 'max:100'],
            'return_address' => ['nullable', 'string', 'max:2000'],
        ]);

        if (empty($validated['order_id']) && empty($validated['order_number'])) {
            return response()->json(['success' => false, 'message' => 'An order id or order number is required.'], 422);
        }

        $order = $this->resolveOrder($request, $validated['order_id'] ?? $validated['order_number']);

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        if ($this->hasOpenReturn($order)) {
            return response()->json(['success' => false, 'message' => 'A return request already exists for this order.'], 422);
        }

        $payload = [
            'return_number' => $this->generateReturnNumber(),
            'user_id' => $request->user()->id,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name ?: $request->user()->name,
            'reason' => $validated['reason'],
            'status' => 'pending',
            'refund_amount' => $validated['refund_amount'] ?? $order->total_amount,
            'refund_method' => $validated['refund_method'] ?? $order->payment_method ?? 'Original Payment Method',
            'currency' => $order->currency ?? 'USD',
            'return_address' => $validated['return_address'] ?? $this->defaultReturnAddress(),
            'requested_at' => now(),
            'return_items' => $this->normalizeReturnItems($order, $validated['return_items'] ?? null),
        ];

        $item = EcommerceReturn::create($this->filterWritableColumns($payload));

        return response()->json([
            'success' => true,
            'message' => 'Return request submitted successfully.',
            'data' => $this->returnPayload($item->fresh(['user', 'order.items.product'])),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $item = $this->resolveReturn($id);

        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Return request not found.'], 404);
        }

        if (! $this->canAccess($request, $item)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json(['success' => true, 'data' => $this->returnPayload($item)]);
    }

    public function update(Request $request, $id)
    {
        $item = $this->resolveReturn($id);

        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Return request not found.'], 404);
        }

        if (! $this->canAccess($request, $item)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (! in_array(strtolower($item->status), ['pending'], true) && ! $request->user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only pending return requests can be edited.'], 422);
        }

        $validated = $request->validate([
            'reason' => ['sometimes', 'string', 'max:1000'],
            'return_items' => ['sometimes', 'array'],
            'refund_method' => ['sometimes', 'nullable', 'string', 'max:100'],
            'return_address' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'status' => ['sometimes', Rule::in(self::ALLOWED_STATUSES)],
        ]);

        if (array_key_exists('status', $validated) && ! $request->user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only admins can change return status.'], 403);
        }

        $item->update($this->filterWritableColumns($validated));

        return response()->json(['success' => true, 'data' => $this->returnPayload($item->fresh(['user', 'order.items.product']))]);
    }

    public function destroy(Request $request, $id)
    {
        $item = $this->resolveReturn($id);

        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Return request not found.'], 404);
        }

        if (! $this->canAccess($request, $item)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $item->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }

    private function resolveOrder(Request $request, string|int|null $id): ?EcommerceOrder
    {
        if (! $id) {
            return null;
        }

        $query = EcommerceOrder::with('items.product');

        if (is_numeric($id)) {
            $query->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('order_number', $id);
            });
        } else {
            $query->where('order_number', $id);
        }

        if (Schema::hasColumn((new EcommerceOrder)->getTable(), 'user_id') && ! $request->user()->isSuperAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        return $query->first();
    }

    private function resolveReturn(string|int $id): ?EcommerceReturn
    {
        return EcommerceReturn::with(['user', 'order.items.product'])
            ->where('id', $id)
            ->orWhere('return_number', $id)
            ->orWhere('order_number', $id)
            ->first();
    }

    private function canAccess(Request $request, EcommerceReturn $return): bool
    {
        $user = $request->user();

        return $user && ($user->isSuperAdmin() || (int) $return->user_id === (int) $user->id);
    }

    private function hasOpenReturn(EcommerceOrder $order): bool
    {
        return EcommerceReturn::query()
            ->where(function ($query) use ($order) {
                $query->where('order_id', $order->id)
                    ->orWhere('order_number', $order->order_number);
            })
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->exists();
    }

    private function normalizeReturnItems(EcommerceOrder $order, ?array $items): array
    {
        if ($items) {
            return array_values(array_map(function ($item) {
                return [
                    'order_item_id' => $item['order_item_id'] ?? null,
                    'product_name' => $item['product_name'] ?? null,
                    'product_sku' => $item['product_sku'] ?? null,
                    'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                    'reason' => $item['reason'] ?? null,
                ];
            }, $items));
        }

        if (! $order->exists) {
            return [];
        }

        return $order->items->map(function ($item) {
            $images = $item->product?->images;

            return [
                'order_item_id' => $item->id,
                'product_name' => $item->product_name,
                'product_sku' => $item->product_sku,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'image' => is_array($images) ? ($images[0] ?? null) : null,
            ];
        })->values()->all();
    }

    private function returnPayload(EcommerceReturn $return): array
    {
        $return->loadMissing(['user', 'order.items.product']);
        $order = $return->order;
        $status = strtolower((string) $return->status);

        return [
            'id' => $return->id,
            'return_number' => $return->return_number,
            'user_id' => $return->user_id,
            'order_id' => $return->order_id,
            'order_number' => $return->order_number ?: $order?->order_number,
            'customer_name' => $return->customer_name ?: $order?->customer_name,
            'reason' => $return->reason,
            'status' => $status,
            'refund_amount' => (float) ($return->refund_amount ?? $order?->total_amount ?? 0),
            'refund_method' => $return->refund_method ?: $order?->payment_method ?: 'Original Payment Method',
            'currency' => $return->currency ?: $order?->currency ?: 'USD',
            'return_address' => $return->return_address ?: $this->defaultReturnAddress(),
            'requested_at' => optional($return->requested_at ?: $return->created_at)->toIso8601String(),
            'approved_at' => optional($return->approved_at)->toIso8601String(),
            'refunded_at' => optional($return->refunded_at)->toIso8601String(),
            'cancelled_at' => optional($return->cancelled_at)->toIso8601String(),
            'created_at' => optional($return->created_at)->toIso8601String(),
            'updated_at' => optional($return->updated_at)->toIso8601String(),
            'order_total' => (float) ($order?->total_amount ?? $return->refund_amount ?? 0),
            'order' => $order,
            'return_items' => $return->return_items ?: $this->normalizeReturnItems($order ?? new EcommerceOrder(), null),
        ];
    }

    private function filterWritableColumns(array $payload): array
    {
        $table = (new EcommerceReturn)->getTable();

        return collect($payload)
            ->filter(fn ($value, $key) => Schema::hasColumn($table, $key))
            ->all();
    }

    private function generateReturnNumber(): string
    {
        do {
            $number = 'RET-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
        } while (EcommerceReturn::where('return_number', $number)->exists());

        return $number;
    }

    private function defaultReturnAddress(): string
    {
        return "Mecarvi Prints\n123 Main St.\nAnytown, CA 12345, USA";
    }
}
