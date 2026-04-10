<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'display_label', 'provider', 'description',
        'public_key', 'secret_key', 'webhook_url',
        'is_active', 'is_test_mode', 'settings', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_test_mode' => 'boolean',
        'settings' => 'array',
    ];

    protected $hidden = ['secret_key'];
}
