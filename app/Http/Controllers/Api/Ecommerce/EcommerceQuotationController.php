<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceQuotation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EcommerceQuotationController extends Controller
{
    public function index(Request $request)
    {
        $quotations = $this->visibleQuery($request)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $quotations]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'quote_number' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'integer'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'customization' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
            'status' => ['nullable', 'string', 'max:80'],
            'total_estimated' => ['nullable', 'numeric', 'min:0'],
            'valid_until' => ['nullable', 'date'],
        ]);

        if (empty($data['quote_number'])) {
            $data['quote_number'] = $this->generateQuoteNumber();
        }

        if (! array_key_exists('status', $data) || blank($data['status'])) {
            $data['status'] = 'Pending Approval';
        }

        if (Schema::hasColumn((new EcommerceQuotation)->getTable(), 'user_id')) {
            $data['user_id'] = $request->user()->id;
        }

        $item = EcommerceQuotation::create($data)->load(['product', 'user:id,name,email']);

        return response()->json(['success' => true, 'data' => $item], 201);
    }

    public function show(Request $request, $id)
    {
        $item = $this->resolveQuotation($request, $id);

        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = $this->resolveQuotation($request, $id);
        $user = $request->user();
        $isAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();

        $rules = [
            'company_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'customer_name' => ['sometimes', 'required', 'string', 'max:255'],
            'contact_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'customer_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'customer_phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'quantity' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'customization' => ['sometimes', 'nullable', 'array'],
            'metadata' => ['sometimes', 'nullable', 'array'],
            'status' => ['sometimes', 'required', 'string', 'max:80'],
            'total_estimated' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'valid_until' => ['sometimes', 'nullable', 'date'],
        ];

        if ($isAdmin) {
            $rules['quote_number'] = ['sometimes', 'required', 'string', 'max:255'];
            $rules['product_id'] = ['sometimes', 'nullable', 'integer'];
        }

        $validated = $request->validate($rules);
        $item->update($validated);
        $item->load(['product', 'user:id,name,email']);

        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy(Request $request, $id)
    {
        $item = $this->resolveQuotation($request, $id);
        $item->delete();

        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }

    private function visibleQuery(Request $request): Builder
    {
        $query = EcommerceQuotation::query()->with(['product', 'user:id,name,email']);
        $user = $request->user();

        if (
            Schema::hasColumn((new EcommerceQuotation)->getTable(), 'user_id') &&
            ! ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
        ) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private function resolveQuotation(Request $request, int|string $id): EcommerceQuotation
    {
        $query = $this->visibleQuery($request);

        if (is_string($id) && str_starts_with($id, 'Q-')) {
            $item = (clone $query)->where('quote_number', $id)->first();
            if ($item) return $item;

            $numId = substr($id, 2);
            if (is_numeric($numId)) {
                $item = (clone $query)->find($numId);
                if ($item) return $item;
            }
        }

        if (is_numeric($id)) {
            return $query->findOrFail($id);
        }

        return $query->where('quote_number', $id)->firstOrFail();
    }

    private function generateQuoteNumber(): string
    {
        do {
            $quoteNumber = 'Q-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
        } while (EcommerceQuotation::query()->where('quote_number', $quoteNumber)->exists());

        return $quoteNumber;
    }
}
