<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceOrderVerification extends Model
{
    use HasFactory;

    protected $table = 'ecommerce_order_verifications';

    protected $fillable = [
        'order_id',
        'order_number',
        'user_id',
        'site_slug',
        'risk_level',
        'flag_reason',
        'reason_title',
        'reason_text',
        'decline_reason',
        'status',
        'deadline_at',
        'verified_at',
        'declined_at',
        'required_documents',
        'submitted_documents',
        'timeline',
        'internal_notes',
        'customer_notes',
        'total_amount',
        'payment_method',
        'product_name',
        'product_specs',
        'item_count',
        'product_image',
    ];

    protected $casts = [
        'required_documents' => 'array',
        'submitted_documents' => 'array',
        'timeline' => 'array',
        'internal_notes' => 'array',
        'total_amount' => 'decimal:2',
        'item_count' => 'integer',
        'deadline_at' => 'datetime',
        'verified_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(EcommerceOrder::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
