<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceQuotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_number',
        'user_id',
        'product_id',
        'company_name',
        'customer_name',
        'contact_email',
        'customer_email',
        'customer_phone',
        'quantity',
        'customization',
        'metadata',
        'status',
        'total_estimated',
        'valid_until',
    ];

    protected $casts = [
        'total_estimated' => 'decimal:2',
        'valid_until' => 'date',
        'quantity' => 'integer',
        'customization' => 'array',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
