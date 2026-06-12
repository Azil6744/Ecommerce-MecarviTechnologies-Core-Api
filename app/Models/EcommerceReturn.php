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
}
