<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceOrderVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'risk_level',
        'flag_reason',
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(EcommerceOrder::class, 'order_id');
    }
}
