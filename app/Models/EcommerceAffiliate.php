<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceAffiliate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'affiliate_code',
        'total_earnings',
        'total_referrals',
        'status',
    ];

    protected $casts = [
        'total_earnings' => 'decimal:2',
        'total_referrals' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
