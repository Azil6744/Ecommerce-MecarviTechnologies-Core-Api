<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceAffiliateApplication extends Model
{
    use HasFactory;

    protected $table = 'ecommerce_affiliate_applications';

    protected $fillable = [
        'user_id',
        'reason',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
