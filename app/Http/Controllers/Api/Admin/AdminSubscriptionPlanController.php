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
        $plans = EcommerceSubscriptionPlan::orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Display active subscription plans for customer-facing pages.
     */
    public function publicIndex()
    {
        $plans = EcommerceSubscriptionPlan::whereIn('status', ['Active', 'Featured'])
            ->where(function ($query) {
                $query->whereNull('effective_date')->orWhere('effective_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('retirement_date')->orWhere('retirement_date', '>', now());
            })
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

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
        $validated = $this->validated($request);

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

        $validated = $this->validated($request, true, $plan->id);

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
        if ($plan->members_limit > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Plans with subscriber history should be retired or archived instead of deleted.',
            ], 409);
        }

        $plan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan deleted successfully.'
        ]);
    }

    private function validated(Request $request, bool $partial = false, ?int $planId = null): array
    {
        $required = $partial ? 'sometimes|required' : 'required';
        $uniqueCode = 'nullable|string|max:100|unique:ecommerce_subscription_plans,internal_code';
        if ($planId) {
            $uniqueCode .= ',' . $planId;
        }

        $validated = $request->validate([
            'name' => "{$required}|string|max:255",
            'internal_code' => $uniqueCode,
            'description' => 'nullable|string|max:4000',
            'account_type' => 'nullable|string|in:personal,business',
            'coverage_type' => 'nullable|string|in:individual_site,universal',
            'applicable_site' => 'nullable|string|max:255|required_if:coverage_type,individual_site',
            'covered_sites' => 'nullable|array',
            'covered_sites.*' => 'nullable|string|max:255',
            'include_future_sites' => 'nullable|boolean',
            'price' => "{$required}|numeric|min:0",
            'billing_cycle' => 'nullable|string|in:Daily,Weekly,Monthly,Quarterly,Six Months,Yearly,Annually,Custom',
            'billing_interval_count' => 'nullable|integer|min:1|max:120',
            'setup_fee' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'tax_treatment' => 'nullable|string|max:100',
            'trial_available' => 'nullable|boolean',
            'trial_duration_days' => 'nullable|integer|min:0|max:730',
            'trial_amount' => 'nullable|numeric|min:0',
            'introductory_price' => 'nullable|numeric|min:0',
            'introductory_duration_days' => 'nullable|integer|min:0|max:730',
            'annual_discount' => 'nullable|numeric|min:0',
            'members_limit' => 'nullable|integer|min:0',
            'tier' => 'nullable|string|max:100',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:255',
            'benefit_config' => 'nullable|array',
            'upgrade_rules' => 'nullable|array',
            'downgrade_rules' => 'nullable|array',
            'cancellation_rules' => 'nullable|array',
            'failed_payment_settings' => 'nullable|array',
            'availability_rules' => 'nullable|array',
            'terms' => 'nullable|string',
            'renewal_disclosure' => 'nullable|string',
            'refund_policy' => 'nullable|string',
            'cancellation_policy' => 'nullable|string',
            'privacy_notice' => 'nullable|string',
            'requires_agreement' => 'nullable|boolean',
            'badge' => 'nullable|string|max:255',
            'image_url' => 'nullable|string|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:Active,Draft,Featured,Inactive,Scheduled,Archived,Retired',
            'effective_date' => 'nullable|date',
            'retirement_date' => 'nullable|date|after_or_equal:effective_date',
        ]);

        foreach (['features', 'covered_sites'] as $arrayField) {
            if (array_key_exists($arrayField, $validated)) {
                $validated[$arrayField] = collect($validated[$arrayField] ?? [])
                    ->filter(fn ($value) => filled($value))
                    ->values()
                    ->all();
            }
        }

        $defaults = [
            'account_type' => 'personal',
            'coverage_type' => 'individual_site',
            'include_future_sites' => false,
            'billing_cycle' => 'Monthly',
            'billing_interval_count' => 1,
            'setup_fee' => 0,
            'currency' => 'USD',
            'trial_available' => false,
            'trial_duration_days' => 0,
            'trial_amount' => 0,
            'introductory_duration_days' => 0,
            'annual_discount' => 0,
            'members_limit' => 0,
            'sort_order' => 0,
            'requires_agreement' => true,
            'status' => 'Active',
        ];

        return $partial ? $validated : array_merge($defaults, $validated);
    }
}
