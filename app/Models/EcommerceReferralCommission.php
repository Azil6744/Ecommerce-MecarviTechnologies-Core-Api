<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceReferralCommission extends Model
{
    use HasFactory;

    protected $table = 'ecommerce_referral_commissions';

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'order_id',
        'order_amount',
        'commission_percentage',
        'commission_amount',
        'status',
        'payout_at',
    ];

    protected $casts = [
        'order_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'payout_at' => 'datetime',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public function order()
    {
        return $this->belongsTo(EcommerceOrder::class, 'order_id');
    }
}
