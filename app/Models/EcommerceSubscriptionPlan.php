<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceSubscriptionPlan extends Model
{
    use HasFactory;

    protected $table = 'ecommerce_subscription_plans';

    protected $fillable = [
        'name',
        'price',
        'billing_cycle',
        'members_limit',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'members_limit' => 'integer',
    ];
}
