<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceSubscriptionPlan;
use Illuminate\Http\Request;

class AdminSubscriptionPlanController extends Controller
{
    /**
     * Display a listing of the subscription plans.
     */
    public function index()
    {
        $plans = EcommerceSubscriptionPlan::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Store a newly created subscription plan in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|string|in:Monthly,Yearly,Weekly,Daily',
            'members_limit' => 'nullable|integer|min:0',
            'status' => 'required|string|in:Active,Draft,Featured,Inactive',
        ]);

        if (!isset($validated['members_limit'])) {
            $validated['members_limit'] = 0;
        }

        $plan = EcommerceSubscriptionPlan::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan created successfully.',
            'data' => $plan
        ], 201);
    }

    /**
     * Display the specified subscription plan.
     */
    public function show(string $id)
    {
        $plan = EcommerceSubscriptionPlan::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $plan
        ]);
    }

    /**
     * Update the specified subscription plan in storage.
     */
    public function update(Request $request, string $id)
    {
        $plan = EcommerceSubscriptionPlan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'billing_cycle' => 'sometimes|required|string|in:Monthly,Yearly,Weekly,Daily',
            'members_limit' => 'nullable|integer|min:0',
            'status' => 'sometimes|required|string|in:Active,Draft,Featured,Inactive',
        ]);

        if (array_key_exists('members_limit', $validated) && $validated['members_limit'] === null) {
            $validated['members_limit'] = 0;
        }

        $plan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan updated successfully.',
            'data' => $plan
        ]);
    }

    /**
     * Remove the specified subscription plan from storage.
     */
    public function destroy(string $id)
    {
        $plan = EcommerceSubscriptionPlan::findOrFail($id);
        $plan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan deleted successfully.'
        ]);
    }
}
