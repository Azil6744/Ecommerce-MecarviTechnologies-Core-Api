<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceLoyaltyTransaction extends Model
{
    use HasFactory;

    protected $table = 'ecommerce_loyalty_transactions';

    protected $fillable = [
        'user_id',
        'order_id',
        'transaction_type',
        'points',
        'dollar_value',
        'status',
        'reason',
        'admin_id',
        'expiration_date',
    ];

    protected $casts = [
        'points' => 'integer',
        'dollar_value' => 'decimal:2',
        'expiration_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order()
    {
        return $this->belongsTo(EcommerceOrder::class, 'order_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
