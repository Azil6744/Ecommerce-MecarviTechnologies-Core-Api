<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Omnipay\Omnipay;
use App\Models\EcommerceOrder;

class PaymentController extends Controller
{
    private $stripeGateway;
    private $paypalGateway;

    public function __construct()
    {
        if (! class_exists(Omnipay::class)) {
            return;
        }

        // Stripe initialization
        $this->stripeGateway = Omnipay::create('Stripe');
        $this->stripeGateway->setApiKey(config('services.stripe.secret'));

        // PayPal initialization
        $this->paypalGateway = Omnipay::create('PayPal_Rest');
        $this->paypalGateway->setClientId(config('services.paypal.client_id'));
        $this->paypalGateway->setSecret(config('services.paypal.secret'));
        $this->paypalGateway->setTestMode(config('services.paypal.mode') === 'sandbox');
    }

    public function showOrder($order)
    {
        $query = EcommerceOrder::query();

        if (is_numeric($order)) {
            $query->where(function ($q) use ($order) {
                $q->where('id', $order)->orWhere('order_number', $order);
            });
        } else {
            $query->where('order_number', $order);
        }

        $item = $query->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $item->id,
                'order_number' => $item->order_number,
                'status' => strtolower((string) $item->status),
                'payment_status' => $item->payment_status,
                'payment_method' => $item->payment_method,
                'currency' => $item->currency ?? 'USD',
                'total_amount' => (float) $item->total_amount,
                'created_at' => optional($item->created_at)->toIso8601String(),
            ],
        ]);
    }

    /**
     * Process a payment using token or redirect.
     */
    public function process(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:ecommerce_orders,id',
            'payment_method' => 'required|in:stripe,paypal',
            'payment_token' => 'required_if:payment_method,stripe|string', // Stripe uses token
        ]);

        $order = EcommerceOrder::findOrFail($request->order_id);

        if ($order->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Order is already paid.'
            ], 400);
        }

        if ($request->payment_method === 'stripe') {
            if (! $this->stripeGateway) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stripe payment gateway is not configured.',
                ], 503);
            }

            return $this->processStripe($order, $request->payment_token);
        } else if ($request->payment_method === 'paypal') {
            if (! $this->paypalGateway) {
                return response()->json([
                    'success' => false,
                    'message' => 'PayPal payment gateway is not configured.',
                ], 503);
            }

            return $this->processPayPal($order);
        }
    }

    /**
     * Process Stripe Payment
     */
    private function processStripe(EcommerceOrder $order, $token)
    {
        if ($this->shouldUseStripeTestMode()) {
            return $this->processStripeTestPayment($order, $token);
        }

        try {
            $response = $this->stripeGateway->purchase([
                'amount' => $order->total_amount,
                'currency' => env('CASHIER_CURRENCY', 'USD'),
                'token' => $token,
                'description' => 'Order #' . $order->order_number,
                'metadata' => [
                    'order_id' => $order->id,
                ]
            ])->send();

            if ($response->isSuccessful()) {
                $order->status = 'paid';
                $order->payment_status = 'paid';
                $order->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful.',
                    'transaction_reference' => $response->getTransactionReference(),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $response->getMessage()
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function shouldUseStripeTestMode(): bool
    {
        return app()->environment(['local', 'testing']) && blank(config('services.stripe.secret'));
    }

    private function processStripeTestPayment(EcommerceOrder $order, string $token)
    {
        $allowedPrefixes = ['test_card_4242424242424242', 'tok_visa'];
        $isApprovedToken = collect($allowedPrefixes)->contains(fn ($prefix) => str_starts_with($token, $prefix));

        if (! $isApprovedToken) {
            return response()->json([
                'success' => false,
                'message' => 'Local Stripe test mode only accepts the 4242 4242 4242 4242 test card.',
            ], 400);
        }

        $order->status = 'paid';
        $order->payment_status = 'paid';
        $order->payment_method = 'stripe';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Test payment successful.',
            'transaction_reference' => 'test_stripe_' . $order->id . '_' . now()->timestamp,
            'test_mode' => true,
        ]);
    }

    /**
     * Process PayPal Payment
     */
    private function processPayPal(EcommerceOrder $order)
    {
        try {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

            $response = $this->paypalGateway->purchase([
                'amount' => $order->total_amount,
                'currency' => env('CASHIER_CURRENCY', 'USD'),
                'description' => 'Order #' . $order->order_number,
                'returnUrl' => $frontendUrl . '/checkout/payment/success?order_id=' . $order->id,
                'cancelUrl' => $frontendUrl . '/checkout/payment/cancel?order_id=' . $order->id,
            ])->send();

            if ($response->isRedirect()) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => $response->getRedirectUrl(),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $response->getMessage()
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle PayPal Success
     */
    public function paypalSuccess(Request $request)
    {
        // For REST api, paymentId and PayerID are returned in the URL
        $paymentId = $request->query('paymentId');
        $payerId = $request->query('PayerID');
        $orderId = $request->query('order_id');

        if (!$paymentId || !$payerId || !$orderId) {
            return response()->json(['success' => false, 'message' => 'Invalid PayPal callback parameters'], 400);
        }

        $order = EcommerceOrder::findOrFail($orderId);

        try {
            $response = $this->paypalGateway->completePurchase([
                'payer_id' => $payerId,
                'transactionReference' => $paymentId,
            ])->send();

            if ($response->isSuccessful()) {
                $order->status = 'paid';
                $order->payment_status = 'paid';
                $order->save();

                return response()->json([
                     'success' => true,
                     'message' => 'PayPal payment successful.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $response->getMessage()
                ], 400);
            }
        } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'message' => $e->getMessage()
             ], 500);
        }
    }

    /**
     * Handle PayPal Cancel
     */
    public function paypalCancel(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'User cancelled the PayPal payment.',
        ]);
    }
}
