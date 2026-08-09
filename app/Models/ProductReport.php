<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReport extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'issue',
        'description',
        'status',
        'report_code',
        'order_number',
        'quantity',
        'purchase_date',
        'issue_type',
        'category',
        'product_name',
        'product_image',
        'customer_name',
        'customer_email',
        'customer_phone',
        'purchase_location',
        'attachments_count',
        'product_images',
        'admin_feedback',
        'customer_replies',
        'status_history',
    ];

    protected $casts = [
        'product_images' => 'array',
        'admin_feedback' => 'array',
        'customer_replies' => 'array',
        'status_history' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
