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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
