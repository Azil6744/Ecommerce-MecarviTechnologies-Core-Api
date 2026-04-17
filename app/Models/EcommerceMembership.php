<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_name',
        'status',
        'next_billing_date',
        'price',
        'billing_cycle',
    ];

    protected $casts = [
        'next_billing_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
