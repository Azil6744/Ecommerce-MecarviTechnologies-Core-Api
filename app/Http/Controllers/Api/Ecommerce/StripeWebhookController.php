<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceSubscriptionPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        // Idempotency check
        $eventId = $request->input('id');
        if ($eventId) {
            if (Cache::has('stripe_processed_event_' . $eventId)) {
                return response()->json(['success' => true, 'message' => 'Event already processed.'], 200);
            }
        }

        // Signature check
        if (!app()->environment('testing') && !empty($endpointSecret)) {
            if (!$this->verifyStripeSignature($request, $endpointSecret)) {
                Log::warning('Stripe webhook signature verification failed.');
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }

        if ($eventId) {
            Cache::put('stripe_processed_event_' . $eventId, true, 86400);
        }

        $event = $request->all();
        $type = $event['type'] ?? '';
        $dataObject = $event['data']['object'] ?? [];

        Log::info("Processing Stripe Webhook: {$type}");

        switch ($type) {
            case 'invoice.payment_succeeded':
                $this->handlePaymentSucceeded($dataObject, $eventId);
                break;
            case 'invoice.payment_failed':
                $this->handlePaymentFailed($dataObject, $eventId);
                break;
            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($dataObject, $eventId);
                break;
            default:
                Log::info("Unhandled Stripe webhook event type: {$type}");
                break;
        }

        return response()->json(['success' => true]);
    }

    private function handlePaymentSucceeded(array $dataObject, ?string $eventId): void
    {
        $email = $dataObject['customer_email'] ?? null;
        if (!$email) {
            Log::warning('Stripe webhook invoice.payment_succeeded missing customer_email.');
            return;
        }

        // Find matching membership
        $membership = $this->findCentralMembershipByEmail($email);
        if (!$membership) {
            Log::warning("No active central membership found for email {$email} on payment success.");
            return;
        }

        // Find cycle to add
        $billingCycle = $membership['billing_cycle'] ?? 'monthly';
        $nextBillingDate = $this->calculateNextPeriodEnd(Carbon::now(), $billingCycle);

        $this->centralCall('post', '/v1/internal/admin/memberships/update', [
            'membership_id' => $membership['id'],
            'email' => $email,
            'plan_name' => $membership['plan_name'],
            'price' => $membership['price'],
            'status' => 'active',
            'current_period_start' => Carbon::now()->toDateTimeString(),
            'current_period_end' => $nextBillingDate->toDateTimeString(),
            'next_billing_date' => $nextBillingDate->toDateTimeString(),
            'transaction_reference' => $dataObject['payment_intent'] ?? $eventId ?? 'stripe_recurring_' . now()->timestamp,
            'transaction_type' => 'renewal',
            'payment_status' => 'paid',
        ]);
    }

    private function handlePaymentFailed(array $dataObject, ?string $eventId): void
    {
        $email = $dataObject['customer_email'] ?? null;
        if (!$email) {
            return;
        }

        $membership = $this->findCentralMembershipByEmail($email);
        if (!$membership) {
            return;
        }

        $this->centralCall('post', '/v1/internal/admin/memberships/update', [
            'membership_id' => $membership['id'],
            'email' => $email,
            'plan_name' => $membership['plan_name'],
            'price' => $membership['price'],
            'status' => 'past_due',
            'transaction_reference' => $eventId ?? 'stripe_fail_' . now()->timestamp,
            'transaction_type' => 'payment_failed',
            'payment_status' => 'failed',
        ]);
    }

    private function handleSubscriptionDeleted(array $dataObject, ?string $eventId): void
    {
        $email = $dataObject['metadata']['customer_email'] ?? null;
        if (!$email) {
            Log::info('Stripe webhook customer.subscription.deleted missing metadata customer_email.');
            return;
        }

        $membership = $this->findCentralMembershipByEmail($email);
        if (!$membership) {
            return;
        }

        $this->centralCall('post', '/v1/internal/admin/memberships/update', [
            'membership_id' => $membership['id'],
            'email' => $email,
            'plan_name' => $membership['plan_name'],
            'price' => $membership['price'],
            'status' => 'canceled',
            'transaction_reference' => $eventId ?? 'stripe_del_' . now()->timestamp,
            'transaction_type' => 'cancellation',
        ]);
    }

    private function findCentralMembershipByEmail(string $email): ?array
    {
        try {
            $response = $this->centralCall('get', '/v1/internal/admin/memberships');
            if ($response->successful() && is_array($response->json('data'))) {
                foreach ($response->json('data') as $m) {
                    if (strtolower($m['user']['email'] ?? '') === strtolower($email) && 
                        in_array(strtolower($m['status'] ?? ''), ['active', 'trialing', 'past_due', 'grace_period', 'pending_upgrade', 'pending_downgrade', 'pending_cancellation'], true)) {
                        return $m;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('StripeWebhookController failed to fetch central memberships: ' . $e->getMessage());
        }
        return null;
    }

    private function centralCall(string $method, string $path, array $data = [])
    {
        $centralUrl = rtrim(config('services.central_auth.url'), '/');
        $secret = (string) config('services.internal_notifications.secret');

        $request = Http::acceptJson()
            ->withHeaders(['X-Internal-Notification-Secret' => $secret])
            ->timeout(5);

        if (strtolower($method) === 'post') {
            return $request->post($centralUrl . $path, $data);
        }
        return $request->get($centralUrl . $path, $data);
    }

    private function calculateNextPeriodEnd(Carbon $start, string $cycle): Carbon
    {
        $date = $start->copy();
        $norm = strtolower(trim($cycle));

        if (str_contains($norm, 'year') || str_contains($norm, 'annual')) {
            return $date->addYear();
        }
        if (str_contains($norm, 'six') || str_contains($norm, '6')) {
            return $date->addMonths(6);
        }
        if (str_contains($norm, 'quarter')) {
            return $date->addMonths(3);
        }
        return $date->addMonth();
    }

    private function verifyStripeSignature(Request $request, string $secret): bool
    {
        $signatureHeader = $request->header('Stripe-Signature');
        if (!$signatureHeader) {
            return false;
        }

        $payload = $request->getContent();
        $parts = explode(',', $signatureHeader);
        $timestamp = null;
        $signatures = [];

        foreach ($parts as $part) {
            $kv = explode('=', $part, 2);
            if (count($kv) === 2) {
                if (trim($kv[0]) === 't') {
                    $timestamp = trim($kv[1]);
                } elseif (trim($kv[0]) === 'v1') {
                    $signatures[] = trim($kv[1]);
                }
            }
        }

        if (!$timestamp || empty($signatures)) {
            return false;
        }

        if (abs(time() - (int)$timestamp) > 300) {
            return false;
        }

        $signedPayload = $timestamp . '.' . $payload;
        $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($signature, $expectedSignature)) {
                return true;
            }
        }

        return false;
    }
}
