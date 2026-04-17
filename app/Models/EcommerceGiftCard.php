<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceGiftCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'recipient_name',
        'recipient_email',
        'sender_name',
        'initial_balance',
        'current_balance',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'expires_at' => 'date',
    ];
}
