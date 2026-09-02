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

    protected $appends = ['has_secret_key', 'masked_secret_key'];

    public function getHasSecretKeyAttribute(): bool
    {
        return !empty($this->secret_key);
    }

    public function getMaskedSecretKeyAttribute(): ?string
    {
        if (empty($this->secret_key)) {
            return null;
        }
        $len = strlen($this->secret_key);
        if ($len <= 8) {
            return '••••••••';
        }
        return substr($this->secret_key, 0, 4) . '••••••••' . substr($this->secret_key, -4);
    }
}
