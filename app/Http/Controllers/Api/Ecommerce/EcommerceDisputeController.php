<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceDispute;
use App\Models\EcommerceOrder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class EcommerceDisputeController extends Controller
{
    private const ALLOWED_STATUSES = ['Open', 'Under Review', 'Awaiting Response', 'Resolved', 'Closed'];

    public function index(Request $request)
    {
        $user = $request->user();

        $query = EcommerceDispute::query()->with(['order.items'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('order_number')) {
            $query->where('order_number', 'like', '%' . $request->string('order_number') . '%');
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('dispute_number', 'like', "%{$search}%")
                    ->orWhere('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $isAdmin = $request->is('*admin/*') || ($user && ($user->hasAdminAccess() || $user->isSuperAdmin()));

        if (!$isAdmin) {
            if ($user) {
                $query->where('user_id', $user->id);
            } else {
                // Public lookup only; never expose full disputes list to guests.
                if (!$request->filled('order_number') && !$request->filled('search')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Guest lookup requires order_number or search.',
                    ], 422);
                }

                $query->whereNotNull('order_number');
            }
        }

        $perPage = max(1, min((int) $request->input('per_page', 15), 100));
        $data = $query->paginate($perPage);

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:100'],
            'name' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'type' => ['nullable', 'string', 'max:255'],
            'issue_type' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'status' => ['nullable', Rule::in(self::ALLOWED_STATUSES)],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'evidence' => ['nullable', 'array', 'max:10'],
            'evidence.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        $user = $request->user();
        $payload = [
            'dispute_number' => $this->generateDisputeNumber(),
            'order_number' => $validated['order_number'],
            'customer_name' => $validated['customer_name'] ?? $validated['name'] ?? ($user?->name),
            'type' => $validated['issue_type'] ?? $validated['type'] ?? $validated['reason'] ?? 'General Dispute',
            'status' => $validated['status'] ?? 'Open',
            'description' => $validated['description'],
        ];

        if ($user && Schema::hasColumn((new EcommerceDispute)->getTable(), 'user_id')) {
            $payload['user_id'] = $user->id;
        }

        $order = EcommerceOrder::where('order_number', $validated['order_number'])->first();
        if ($order && !$payload['customer_name']) {
            $payload['customer_name'] = $order->customer_name ?? $user?->name;
        }

        if (Schema::hasColumn((new EcommerceDispute)->getTable(), 'email') && isset($validated['email'])) {
            $payload['email'] = $validated['email'];
        }

        if (Schema::hasColumn((new EcommerceDispute)->getTable(), 'phone') && isset($validated['phone'])) {
            $payload['phone'] = $validated['phone'];
        }

        if (Schema::hasColumn((new EcommerceDispute)->getTable(), 'amount') && isset($validated['amount'])) {
            $payload['amount'] = $validated['amount'];
        }

        if (Schema::hasColumn((new EcommerceDispute)->getTable(), 'evidence') && $request->hasFile('evidence')) {
            $files = [];
            foreach ($request->file('evidence') as $file) {
                $files[] = $file->store('disputes/evidence', 'public');
            }
            $payload['evidence'] = $files;
        }

        $item = EcommerceDispute::create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Dispute submitted successfully.',
            'data' => [
                'dispute' => $item,
                'reference' => $item->dispute_number,
            ],
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $item = $this->resolveDispute($id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Dispute not found'], 404);
        }

        if (!$this->canAccess($request, $item)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = $this->resolveDispute($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Dispute not found'], 404);
        }

        $user = $request->user();
        if (!$this->canAccess($request, $item)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $rules = [
            'description' => ['sometimes', 'string', 'max:5000'],
            'type' => ['sometimes', 'string', 'max:255'],
            'issue_type' => ['sometimes', 'string', 'max:255'],
            'customer_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            'amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::in(self::ALLOWED_STATUSES)],
            'evidence' => ['nullable', 'array', 'max:10'],
            'evidence.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ];
        $validated = $request->validate($rules);

        if ((!$user || !$user->isSuperAdmin()) && array_key_exists('status', $validated)) {
            return response()->json(['success' => false, 'message' => 'Only admins can change dispute status'], 403);
        }

        if (isset($validated['issue_type']) && !isset($validated['type'])) {
            $validated['type'] = $validated['issue_type'];
            unset($validated['issue_type']);
        }

        if (Schema::hasColumn((new EcommerceDispute)->getTable(), 'evidence') && $request->hasFile('evidence')) {
            $files = is_array($item->evidence) ? $item->evidence : [];
            foreach ($request->file('evidence') as $file) {
                $files[] = $file->store('disputes/evidence', 'public');
            }
            $validated['evidence'] = $files;
        }

        $item->update($validated);
        return response()->json(['success' => true, 'data' => $item->fresh(['order.items'])]);
    }

    public function destroy(Request $request, $id)
    {
        $item = $this->resolveDispute($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Dispute not found'], 404);
        }

        if (!$this->canAccess($request, $item)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $item->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }

    private function canAccess(Request $request, EcommerceDispute $dispute): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin() || ((int) $dispute->user_id === (int) $user->id);
    }

    private function resolveDispute(string $id): ?EcommerceDispute
    {
        return EcommerceDispute::query()
            ->with(['order.items'])
            ->where(function ($query) use ($id) {
                $query->where('id', $id)
                    ->orWhere('dispute_number', $id)
                    ->orWhere('order_number', $id);
            })
            ->first();
    }

    private function generateDisputeNumber(): string
    {
        do {
            $value = 'DSP-' . now()->format('Ymd') . '-' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (EcommerceDispute::where('dispute_number', $value)->exists());

        return $value;
    }
}
