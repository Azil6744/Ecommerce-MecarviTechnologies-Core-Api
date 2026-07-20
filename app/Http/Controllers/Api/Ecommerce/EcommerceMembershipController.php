<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceSubscriptionPlan;
use App\Services\StripeMembershipPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EcommerceMembershipController extends Controller
{
    private function centralCall(string $method, string $path, array $data = [], ?string $token = null)
    {
        $centralUrl = rtrim(config('services.central_auth.url'), '/');
        
        if ($token) {
            $request = Http::acceptJson()
                ->withToken($token)
                ->timeout(5);
        } else {
            $secret = (string) config('services.internal_notifications.secret');
            $request = Http::acceptJson()
                ->withHeaders(['X-Internal-Notification-Secret' => $secret])
                ->timeout(5);
        }

        if (strtolower($method) === 'post') {
            return $request->post($centralUrl . $path, $data);
        }

        return $request->get($centralUrl . $path, $data);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $token = $request->bearerToken();

        $isAdminRequest = str_contains($request->path(), '/admin/memberships');

        // If admin / superadmin, call the internal admin endpoint to list all.
        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin() && $isAdminRequest) {
            try {
                $response = $this->centralCall('get', '/v1/internal/admin/memberships');
                if ($response->successful()) {
                    return response()->json([
                        'success' => true,
                        'data' => $response->json('data'),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Central memberships admin index failed: ' . $e->getMessage());
            }
            return response()->json(['success' => true, 'data' => []]);
        }

        // Customer query
        try {
            if ($token) {
                $response = $this->centralCall('get', '/user/memberships', [], $token);
            } else {
                $response = $this->centralCall('get', '/v1/internal/admin/memberships');
                if ($response->successful()) {
                    $filtered = collect($response->json('data'))->filter(fn($m) => strtolower($m['user']['email'] ?? '') === strtolower($user->email))->values();
                    return response()->json([
                        'success' => true,
                        'data' => $filtered,
                    ]);
                }
            }

            if ($response && $response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json('data'),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Central memberships index failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'data' => []]);
    }

    public function store(Request $request)
    {
        $token = $request->bearerToken();
        $user = $request->user();
        $payload = $this->membershipPayload($request);

        try {
            if ($request->filled('plan_id')) {
                $plan = EcommerceSubscriptionPlan::whereIn('status', ['Active', 'Featured'])
                    ->findOrFail($request->integer('plan_id'));
                $payment = app(StripeMembershipPaymentService::class)->authorizePlanPurchase($plan, $request);
                $payload = array_merge($payload, [
                    'payment_status' => $payment['payment_status'],
                    'payment_method' => $payment['payment_method'],
                    'payment_processor' => $payment['payment_processor'],
                    'transaction_reference' => $payment['transaction_reference'],
                    'payment_summary' => array_merge($payload['payment_summary'] ?? [], [
                        'payment_processor' => $payment['payment_processor'],
                        'transaction_reference' => $payment['transaction_reference'],
                        'stripe' => $payment['payment_details'],
                    ]),
                ]);
            }

            if ($token) {
                $response = $this->centralCall('post', '/user/memberships', $payload, $token);
            } else {
                $response = $this->centralCall('post', '/v1/internal/admin/memberships/update', array_merge($payload, ['email' => $user->email]));
            }

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json('data'),
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => $response->json('message') ?: 'Failed to store membership',
            ], $response->status());
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        return response()->json(['success' => false, 'message' => 'Method not supported in centralized mode.'], 501);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $payload = $this->membershipPayload($request);
        try {
            $response = $this->centralCall('post', '/v1/internal/admin/memberships/update', array_merge($payload, ['membership_id' => $id]));
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json('data'),
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => $response->json('message') ?: 'Failed to update membership',
            ], $response->status());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        return response()->json(['success' => false, 'message' => 'Method not supported in centralized mode.'], 501);
    }

    public function adminTransactions(Request $request)
    {
        try {
            $response = $this->centralCall('get', '/v1/internal/admin/membership-transactions');
            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function transactions(Request $request)
    {
        $token = $request->bearerToken();
        if (! $token) {
            return response()->json(['success' => false, 'message' => 'Authenticated session is required.'], 401);
        }

        try {
            $response = $this->centralCall('get', '/user/membership-transactions', [], $token);
            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function receipt(Request $request, $id)
    {
        $token = $request->bearerToken();
        if (! $token) {
            return response()->json(['success' => false, 'message' => 'Authenticated session is required.'], 401);
        }

        try {
            $response = $this->centralCall('get', "/user/membership-transactions/{$id}/receipt", [], $token);
            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function adminReceipt(Request $request, $id)
    {
        try {
            $response = $this->centralCall('get', "/v1/internal/admin/membership-transactions/{$id}/receipt");
            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function adminAuditLogs(Request $request)
    {
        try {
            $response = $this->centralCall('get', '/v1/internal/admin/membership-audit-logs');
            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function action(Request $request, $id, string $action)
    {
        $token = $request->bearerToken();
        if (! $token) {
            return response()->json(['success' => false, 'message' => 'Authenticated session is required.'], 401);
        }

        try {
            $response = $this->centralCall('post', "/user/memberships/{$id}/{$action}", $request->all(), $token);
            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function membershipPayload(Request $request): array
    {
        if ($request->filled('plan_id')) {
            $plan = EcommerceSubscriptionPlan::whereIn('status', ['Active', 'Featured'])
                ->findOrFail($request->integer('plan_id'));

            return [
                'plan_name' => $plan->name,
                'status' => 'Active',
                'price' => $plan->price,
                'billing_cycle' => $plan->billing_cycle,
                'next_billing_date' => $request->input('next_billing_date'),
                'payment_status' => $request->input('payment_status', 'paid'),
                'payment_method' => $request->input('payment_method', 'card'),
                'transaction_reference' => $request->input('transaction_reference', 'membership_' . $plan->id . '_' . now()->timestamp),
                'plan_id' => $plan->id,
                'plan_code' => $plan->internal_code,
                'account_type' => $plan->account_type,
                'coverage_type' => $plan->coverage_type,
                'applicable_site' => $plan->applicable_site,
                'covered_sites' => $plan->covered_sites,
                'benefits_snapshot' => $plan->benefit_config ?: $plan->features,
                'payment_summary' => $request->input('payment_summary'),
                'terms_accepted' => $request->boolean('terms_accepted'),
                'pin' => $request->input('pin'),
                'currency' => $plan->currency ?? 'USD',
            ];
        }

        return $request->only([
            'membership_id',
            'plan_id',
            'plan_name',
            'plan_code',
            'account_type',
            'coverage_type',
            'applicable_site',
            'covered_sites',
            'status',
            'price',
            'billing_cycle',
            'next_billing_date',
            'current_period_start',
            'current_period_end',
            'final_access_at',
            'cancel_at_period_end',
            'scheduled_plan',
            'benefits_snapshot',
            'payment_summary',
            'payment_status',
            'payment_method',
            'payment_processor',
            'transaction_reference',
            'transaction_type',
            'discount_amount',
            'tax_amount',
            'credit_applied',
            'refund_amount',
            'currency',
            'terms_accepted',
            'pin',
            'authorization_reference',
            'timezone',
            'metadata',
        ]);
    }
}
