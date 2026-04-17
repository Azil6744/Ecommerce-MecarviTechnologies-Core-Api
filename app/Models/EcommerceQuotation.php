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
        'company_name',
        'customer_name',
        'contact_email',
        'status',
        'total_estimated',
        'valid_until',
    ];

    protected $casts = [
        'total_estimated' => 'decimal:2',
        'valid_until' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
