<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceDispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispute_number',
        'user_id',
        'order_number',
        'customer_name',
        'type',
        'status',
        'description',
        'email',
        'phone',
        'amount',
        'evidence',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'evidence' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(EcommerceOrder::class, 'order_number', 'order_number');
    }
}
