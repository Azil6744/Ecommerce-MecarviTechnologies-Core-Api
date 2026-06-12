<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceGiftCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'code',
        'recipient_name',
        'recipient_email',
        'sender_name',
        'initial_balance',
        'current_balance',
        'status',
        'expires_at',
        'delivery_type',
        'message',
        'scheduled_for',
        'purchased_at',
        'redeemed_at',
        'currency',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'expires_at' => 'date',
        'scheduled_for' => 'datetime',
        'purchased_at' => 'datetime',
        'redeemed_at' => 'datetime',
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
