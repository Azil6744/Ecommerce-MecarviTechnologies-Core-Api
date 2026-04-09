<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceQuotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'description',
        'required_quantity',
        'required_specification',
        'delivery_date',
        'status',
        'quote_price',
        'quote_details',
        'quoted_at',
        'validity_date',
        'expires_at',
    ];

    protected $casts = [
        'quote_price' => 'decimal:2',
        'quoted_at' => 'datetime',
        'expires_at' => 'datetime',
        'delivery_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
