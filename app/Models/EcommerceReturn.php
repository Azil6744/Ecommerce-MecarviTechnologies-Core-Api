<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_number',
        'user_id',
        'order_id',
        'order_number',
        'customer_name',
        'reason',
        'return_items',
        'status',
        'refund_amount',
        'refund_method',
        'currency',
        'return_address',
        'requested_at',
        'approved_at',
        'refunded_at',
        'cancelled_at',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'return_items' => 'array',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'refunded_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(EcommerceOrder::class, 'order_id');
    }

    protected static function booted()
    {
        static::updated(function ($return) {
            // Trigger deduction when return is approved, completed, or refunded
            if ($return->wasChanged('status') && in_array($return->status, ['approved', 'completed', 'refunded'], true)) {
                try {
                    \DB::transaction(function () use ($return) {
                        $commission = \App\Models\EcommerceReferralCommission::where('order_id', $return->order_id)
                            ->where('status', '!=', 'cancelled')
                            ->first();

                        if ($commission) {
                            $order = $return->order;
                            $orderTotal = (float)($order ? ($order->subtotal ?: $order->total_amount) : $commission->order_amount);
                            $refundAmount = (float)$return->refund_amount;

                            // If refund amount is zero or covers the full order, treat as full refund
                            $isFullRefund = ($refundAmount <= 0) || ($refundAmount >= $orderTotal);
                            
                            $deduction = $isFullRefund 
                                ? (float)$commission->commission_amount 
                                : round((float)$commission->commission_amount * ($refundAmount / ($orderTotal ?: 1.00)), 2);

                            $deduction = min((float)$commission->commission_amount, $deduction);

                            if ($deduction > 0) {
                                if ($commission->status === 'pending') {
                                    if ($isFullRefund || $deduction >= (float)$commission->commission_amount) {
                                        $commission->update([
                                            'commission_amount' => 0.00,
                                            'status' => 'cancelled'
                                        ]);
                                    } else {
                                        $commission->update([
                                            'commission_amount' => round((float)$commission->commission_amount - $deduction, 2)
                                        ]);
                                    }
                                } elseif ($commission->status === 'completed') {
                                    $referrer = \App\Models\User::find($commission->referrer_id);
                                    if ($referrer) {
                                        $newBalance = round((float)($referrer->wallet_balance ?? 0) - $deduction, 2);
                                        $referrer->update(['wallet_balance' => $newBalance]);

                                        \App\Models\EcommerceWalletTransaction::create([
                                            'user_id' => $referrer->id,
                                            'type' => 'Affiliate Deduction',
                                            'amount' => -$deduction,
                                            'balance_after' => $newBalance,
                                            'description' => 'Commission deduction for order #' . ($order ? $order->order_number : $return->order_number) . ' return',
                                            'status' => 'Completed',
                                        ]);
                                    }

                                    if ($isFullRefund || $deduction >= (float)$commission->commission_amount) {
                                        $commission->update([
                                            'commission_amount' => 0.00,
                                            'status' => 'cancelled'
                                        ]);
                                    } else {
                                        $commission->update([
                                            'commission_amount' => round((float)$commission->commission_amount - $deduction, 2)
                                        ]);
                                    }
                                }
                            }
                        }
                    });
                } catch (\Exception $e) {
                    \Log::error('Referral commission deduction failed: ' . $e->getMessage());
                }
            }
        });
    }
}
