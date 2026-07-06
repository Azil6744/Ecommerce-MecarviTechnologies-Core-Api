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
        'quote_price',
        'quote_details',
        'quoted_at',
    ];

    protected $casts = [
        'total_estimated' => 'decimal:2',
        'valid_until' => 'date',
        'quantity' => 'integer',
        'customization' => 'array',
        'metadata' => 'array',
        'quote_price' => 'decimal:2',
        'quoted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (is_string($value) && str_starts_with($value, 'Q-')) {
            $item = $this->where('quote_number', $value)->first();
            if ($item) return $item;

            $numId = substr($value, 2);
            if (is_numeric($numId)) {
                $item = $this->find($numId);
                if ($item) return $item;
            }
        }

        if (is_numeric($value)) {
            return $this->where('id', $value)->first();
        }

        return $this->where('quote_number', $value)->first();
    }
}
