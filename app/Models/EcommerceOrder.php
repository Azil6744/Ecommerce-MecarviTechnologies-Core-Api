<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tip_amount' => 'decimal:2',
        'donation_amount' => 'decimal:2',
        'loyalty_points_earned' => 'integer',
        'loyalty_points_redeemed' => 'integer',
        'order_date' => 'datetime',
        'estimated_delivery_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationship with user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with order items
    public function items()
    {
        return $this->hasMany(EcommerceOrderItem::class, 'order_id');
    }

    // Generate order number
    public static function generateOrderNumber()
    {
        return 'ORD-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function proofs()
    {
        return $this->hasMany(EcommerceOrderProof::class, 'order_id');
    }

    public function verifications()
    {
        return $this->hasMany(EcommerceOrderVerification::class, 'order_id');
    }

    public function statusEvents()
    {
        return $this->hasMany(EcommerceOrderStatusEvent::class, 'order_id')->latest();
    }

    protected static function booted()
    {
        static::updated(function ($order) {
            // Trigger referral commission logic when payment_status becomes 'paid'
            if ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
                try {
                    // Check if referred user exists in referrals table
                    $referral = \App\Models\EcommerceReferral::where('referred_id', $order->user_id)->first();
                    if ($referral && $referral->referrer_id) {
                        // Check if a commission has already been calculated for this order to prevent duplication
                        $exists = \App\Models\EcommerceReferralCommission::where('order_id', $order->id)->exists();
                        if (!$exists) {
                            $settings = \App\Models\SiteSetting::first();
                            $commissionPercentage = $settings ? (float)$settings->referral_commission_percentage : 0.00;

                            if ($commissionPercentage > 0) {
                                $orderAmount = (float)($order->subtotal ?: $order->total_amount);
                                $commissionAmount = round($orderAmount * ($commissionPercentage / 100), 2);

                                if ($commissionAmount > 0) {
                                    \App\Models\EcommerceReferralCommission::create([
                                        'referrer_id' => $referral->referrer_id,
                                        'referred_id' => $order->user_id,
                                        'order_id' => $order->id,
                                        'order_amount' => $orderAmount,
                                        'commission_percentage' => $commissionPercentage,
                                        'commission_amount' => $commissionAmount,
                                        'status' => 'pending',
                                        'payout_at' => now()->addDays(10),
                                    ]);
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Referral commission failed: ' . $e->getMessage());
                }
            }
        });
    }
}
