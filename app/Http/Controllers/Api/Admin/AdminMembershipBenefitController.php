<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceMembershipBenefit;
use Illuminate\Http\Request;

class AdminMembershipBenefitController extends Controller
{
    public function index()
    {
        $benefits = EcommerceMembershipBenefit::orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $benefits,
        ]);
    }

    public function publicIndex()
    {
        $benefits = EcommerceMembershipBenefit::where('status', '!=', 'Inactive')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $benefits,
        ]);
    }

    public function store(Request $request)
    {
        $benefit = EcommerceMembershipBenefit::create($this->validated($request));

        return response()->json([
            'success' => true,
            'message' => 'Membership benefit created successfully.',
            'data' => $benefit,
        ], 201);
    }

    public function show(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => EcommerceMembershipBenefit::findOrFail($id),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $benefit = EcommerceMembershipBenefit::findOrFail($id);
        $benefit->update($this->validated($request, true));

        return response()->json([
            'success' => true,
            'message' => 'Membership benefit updated successfully.',
            'data' => $benefit->fresh(),
        ]);
    }

    public function destroy(string $id)
    {
        EcommerceMembershipBenefit::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Membership benefit deleted successfully.',
        ]);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes|required' : 'required';

        return $request->validate([
            'title' => "{$required}|string|max:255",
            'description' => 'nullable|string|max:1000',
            'benefit_type' => 'nullable|string|in:percentage_discount,fixed_discount,free_delivery,discounted_delivery,free_setup,discounted_setup,free_digitizing,discounted_digitizing,loyalty_multiplier,monthly_reward_points,member_only_pricing,priority_production,reduced_rush_fee,free_revisions,file_storage_limit,quote_limit,early_access,exclusive_products,exclusive_promotions,dedicated_support,monthly_store_credit,annual_store_credit,gift_card_benefit,affiliate_boost',
            'benefit_value' => 'nullable|numeric|min:0',
            'restrictions' => 'nullable|array',
            'usage_limit' => 'nullable|integer|min:0',
            'reset_frequency' => 'nullable|string|in:none,order,daily,monthly,annual',
            'eligible_websites' => 'nullable|array',
            'eligible_websites.*' => 'nullable|string|max:255',
            'eligible_products' => 'nullable|array',
            'eligible_products.*' => 'nullable',
            'eligible_categories' => 'nullable|array',
            'eligible_categories.*' => 'nullable',
            'stacking_rules' => 'nullable|array',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'background' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:Active,Draft,Inactive',
        ]);
    }
}
