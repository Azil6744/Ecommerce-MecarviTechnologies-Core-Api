<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'product_id',
        'order_id',
        'customer_name',
        'contact_email',
        'contact_phone',
        'preferred_contact_method',
        'subject',
        'category',
        'priority',
        'is_urgent',
        'status',
        'message',
        'source_page',
        'metadata',
        'last_customer_reply_at',
        'last_staff_reply_at',
        'closed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_urgent' => 'boolean',
        'last_customer_reply_at' => 'datetime',
        'last_staff_reply_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(EcommerceTicketReply::class);
    }

    public function attachments()
    {
        return $this->hasMany(EcommerceTicketAttachment::class);
    }

    public function activities()
    {
        return $this->hasMany(EcommerceTicketActivity::class);
    }

    public function notes()
    {
        return $this->hasMany(EcommerceTicketNote::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(EcommerceOrder::class, 'order_id');
    }
}
