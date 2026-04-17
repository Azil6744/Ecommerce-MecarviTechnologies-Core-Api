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
        'order_number',
        'customer_name',
        'reason',
        'status',
        'refund_amount',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
